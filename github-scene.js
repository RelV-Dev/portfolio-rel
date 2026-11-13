/* Contribution Orbit. Three.js r128, loaded only near the GitHub section. */
(() => {
  'use strict';
  const DAY = 86400000;
  const palette = ['#19363e', '#186878', '#239caa', '#51d3c0', '#b5f3cb'];
  const format = new Intl.NumberFormat('id-ID');
  const dateFormat = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
  const shortDate = new Intl.DateTimeFormat('id-ID', { month: 'short', year: '2-digit', timeZone: 'UTC' });

  // Position by calendar day, not array index; missing API days remain empty.
  function normalizeDays(input) {
    const dates = new Map();
    for (const entry of Array.isArray(input) ? input : []) {
      if (!entry || !/^\d{4}-\d{2}-\d{2}$/.test(entry.date)) continue;
      const stamp = Date.parse(entry.date + 'T00:00:00Z');
      const count = Number(entry.count);
      if (!Number.isFinite(stamp) || new Date(stamp).toISOString().slice(0, 10) !== entry.date || !Number.isFinite(count) || count < 0) continue;
      dates.set(entry.date, { date: entry.date, stamp, count: Math.floor(count) });
    }
    const sorted = [...dates.values()].sort((a, b) => a.stamp - b.stamp);
    if (!sorted.length) return [];
    const cutoff = sorted[sorted.length - 1].stamp - 363 * DAY;
    return sorted.filter(day => day.stamp >= cutoff);
  }

  function mount(container, data) {
    if (container._githubScene) return container._githubScene;
    const T = window.THREE;
    const days = normalizeDays(data.contributionGraph);
    const hasData = days.length > 0;
    // Layout reference only: no fabricated bars or contribution values.
    const referenceDay = {stamp:Date.UTC(2026,0,1)};
    const panel = container.closest('.github-card-container');
    const $ = selector => panel.querySelector(selector);
    const motionQuery = matchMedia('(prefers-reduced-motion: reduce)');
    const compact = matchMedia('(max-width: 600px)').matches;
    let reduced = motionQuery.matches, paused = reduced, visible = false, lost = false, disposed = false;
    let frame = 0, lastFrame = 0, elapsed = 0, intro = reduced ? 2 : 0, selected = days.length - 1;
    let cameraTransition = null, greeting = 0, flight = 0, orbitEnabled = true, view = 'orbit';
    const cleanups = [];
    const on = (target, event, handler, options) => {
      target.addEventListener(event, handler, options);
      cleanups.push(() => target.removeEventListener(event, handler, options));
    };
    const renderer = new T.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'low-power' });
    renderer.setPixelRatio(Math.min(devicePixelRatio || 1, compact ? 1.25 : 1.5));
    renderer.outputEncoding = T.sRGBEncoding;
    renderer.toneMapping = T.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    const canvas = renderer.domElement;
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', 'Kota kontribusi 3D. Gunakan pemilih tanggal di bawah grafik untuk membaca data setiap hari.');
    container.replaceChildren(canvas);
    const scene = new T.Scene();
    const camera = new T.PerspectiveCamera(38, 1, 0.1, 180);
    const controls = new T.OrbitControls(camera, canvas);
    controls.enableZoom = false; // Wheel/touch scrolling belongs to the page.
    controls.enablePan = false;
    controls.enableDamping = false;
    controls.rotateSpeed = 0.45;
    controls.minPolarAngle = 0.04;
    controls.maxPolarAngle = Math.PI * 0.47;
    // Touch uses view buttons + date slider; vertical swipes remain page scrolls.
    controls.touches.ONE = undefined;
    controls.touches.TWO = undefined;
    canvas.style.touchAction = 'pan-y';
    scene.add(new T.HemisphereLight(0xc6efff, 0x15132c, 0.85));
    const sun = new T.DirectionalLight(0xf4fff4, 1.25);
    sun.position.set(-8, 20, 12); scene.add(sun);
    const rim = new T.DirectionalLight(0x777bff, 0.8);
    rim.position.set(12, 8, -10); scene.add(rim);

    const world = new T.Group(); scene.add(world);
    const chart = new T.Group(); world.add(chart);
    const box = new T.BoxGeometry(1, 1, 1);
    const material = (color, extra = {}) => new T.MeshStandardMaterial({ color, roughness: 0.38, metalness: 0.38, ...extra });
    function block(parent, size, position, mat) {
      const mesh = new T.Mesh(box, mat); mesh.scale.set(...size); mesh.position.set(...position); parent.add(mesh); return mesh;
    }
    function line(points, color, opacity = 1, parent = world) {
      const geometry = new T.BufferGeometry().setFromPoints(points.map(p => new T.Vector3(...p)));
      const mesh = new T.Line(geometry, new T.LineBasicMaterial({ color, transparent: true, opacity })); parent.add(mesh); return mesh;
    }
    function ellipse(rx, rz, y, color, opacity) {
      return line(Array.from({ length: 161 }, (_, i) => {
        const a = i / 160 * Math.PI * 2; return [Math.cos(a) * rx, y, Math.sin(a) * rz];
      }), color, opacity);
    }
    function label(text, x, y, z, color = '#9cb9c3', width = 2.4) {
      const c = document.createElement('canvas'); c.width = 512; c.height = 64;
      const context = c.getContext('2d'); context.fillStyle = color;
      context.font = '500 29px monospace'; context.textAlign = 'center'; context.textBaseline = 'middle'; context.fillText(text, 256, 32);
      const texture = new T.CanvasTexture(c); texture.encoding = T.sRGBEncoding;
      const sprite = new T.Sprite(new T.SpriteMaterial({ map: texture, transparent: true, depthWrite: false }));
      sprite.position.set(x, y, z); sprite.scale.set(width, width / 8, 1); world.add(sprite); return sprite;
    }

    const first = days[0] || referenceDay, last = days[days.length - 1] || referenceDay;
    const start = first.stamp - new Date(first.stamp).getUTCDay() * DAY;
    const weeks = hasData ? Math.floor((last.stamp - start) / (7 * DAY)) + 1 : 53;
    const stepX = Math.min(0.43, 21.8 / weeks), stepZ = 0.72;
    const spanX = Math.max(8, weeks * stepX + 2.2), spanZ = 7.2;
    const maxCount = Math.max(1, ...days.map(d => d.count));
    const coordinates = days.map(day => {
      const col = Math.floor((day.stamp - start) / (7 * DAY));
      const row = new Date(day.stamp).getUTCDay();
      const ratio = day.count / maxCount;
      return { x: (col - (weeks - 1) / 2) * stepX, z: (row - 3) * stepZ,
        height: day.count === 0 ? 0.09 : 0.22 + Math.sqrt(ratio) * 3.1,
        color: new T.Color(palette[day.count === 0 ? 0 : Math.min(4, Math.ceil(ratio * 4))]), delay: col / Math.max(1, weeks - 1) * 0.6 + row * 0.025 };
    });

    // A floating, layered deck keeps the calendar prominent among the orbits.
    block(chart, [spanX, 0.3, spanZ], [0, -0.25, 0], material(0x101e2b));
    block(chart, [spanX - 0.3, 0.22, spanZ - 0.3], [0, -0.5, 0], material(0x060d17));
    const edge = new T.LineSegments(new T.EdgesGeometry(new T.BoxGeometry(spanX, 0.32, spanZ)), new T.LineBasicMaterial({ color: 0x60c9d0, transparent: true, opacity: 0.45 }));
    edge.position.y = -0.24; chart.add(edge);
    block(chart, [spanX - 0.7, 0.025, 0.035], [0, -0.42, spanZ / 2 + 0.01], new T.MeshBasicMaterial({ color: 0x7adfd3 }));
    for (let row = 0; row <= 7; row++) {
      line([[-spanX / 2 + 0.45, -0.075, (row - 3.5) * stepZ], [spanX / 2 - 0.45, -0.075, (row - 3.5) * stepZ]], 0x467d89, 0.18, chart);
    }
    const barMat = material(0xffffff, { metalness: 0.25, roughness: 0.3 });
    const bars = new T.InstancedMesh(box, barMat, days.length);
    bars.instanceMatrix.setUsage(T.DynamicDrawUsage);
    // r128 computes a shared geometry sphere, not the animated instance bounds.
    bars.frustumCulled = false;
    const caps = new T.InstancedMesh(box, new T.MeshBasicMaterial({ color: 0xffffff }), days.length);
    caps.instanceMatrix.setUsage(T.DynamicDrawUsage); caps.frustumCulled = false;
    const dummy = new T.Object3D();
    chart.add(bars, caps);
    coordinates.forEach((p, i) => { bars.setColorAt(i, p.color); caps.setColorAt(i, p.color.clone().lerp(new T.Color('#d8fff1'), days[i].count ? 0.25 : 0)); });
    function buildBars() {
      coordinates.forEach((p, i) => {
        const progress = Math.min(1, Math.max(0, (intro - p.delay) / 0.85));
        const h = Math.max(0.015, p.height * (1 - Math.pow(1 - progress, 3)));
        dummy.position.set(p.x, h / 2, p.z); dummy.scale.set(stepX * 0.77, h, stepZ * 0.76); dummy.updateMatrix(); bars.setMatrixAt(i, dummy.matrix);
        dummy.position.y = h + 0.012; dummy.scale.y = 0.025; dummy.updateMatrix(); caps.setMatrixAt(i, dummy.matrix);
      });
      bars.instanceMatrix.needsUpdate = true; caps.instanceMatrix.needsUpdate = true;
    }
    buildBars();
    let previousMonth = '';
    days.forEach((d, i) => {
      const month = d.date.slice(0, 7);
      if (month !== previousMonth && (weeks < 16 || Number(d.date.slice(5, 7)) % 2 === 1)) {
        label(shortDate.format(d.stamp).toUpperCase(), coordinates[i].x, -0.02, -3.15, '#a0bdc4', 2.1);
      }
      previousMonth = month;
    });
    label('CONTRIBUTION ARCHIVE', 0, -0.85, 4.1, '#668c9e', 5.5);

    // Two fine orbital tracks, restrained particles, and a ringed planet.
    ellipse(14.8, 7.0, -0.9, 0x6e91a0, 0.24);
    ellipse(17.3, 8.5, -1.1, 0x8d7aca, 0.15).rotation.y = -0.18;
    const satellite = new T.Mesh(new T.SphereGeometry(0.13, 12, 8), new T.MeshBasicMaterial({ color: 0xa5f7e4 })); world.add(satellite);
    const planet = new T.Group(); planet.position.set(10, 3.9, -5.6); world.add(planet);
    const sphere = new T.Mesh(new T.SphereGeometry(1.05, 32, 20), material(0x9486ca, { roughness: 0.72 })); planet.add(sphere);
    const rings = new T.Mesh(new T.RingGeometry(1.45, 2.12, 64), new T.MeshBasicMaterial({ color: 0xaaa1e8, side: T.DoubleSide, transparent: true, opacity: 0.38, depthWrite: false }));
    rings.rotation.x = Math.PI * 0.39; rings.rotation.y = -0.2; planet.add(rings);
    const moon = new T.Mesh(new T.SphereGeometry(0.28, 16, 12), material(0x9bded7)); moon.position.set(-11.7, 2.3, -4.5); world.add(moon);
    const points = new Float32Array(210 * 3);
    // Fixed seed avoids a different composition on each reload.
    let seed = 42; const random = () => { seed = (seed * 1664525 + 1013904223) >>> 0; return seed / 4294967296; };
    for (let i = 0; i < points.length; i += 3) { points[i] = (random() - 0.5) * 65; points[i + 1] = random() * 18 - 3; points[i + 2] = -8 - random() * 20; }
    const starGeometry = new T.BufferGeometry(); starGeometry.setAttribute('position', new T.BufferAttribute(points, 3));
    const stars = new T.Points(starGeometry, new T.PointsMaterial({ color: 0xc2d8ee, size: 0.055, transparent: true, opacity: 0.65 })); scene.add(stars);

    // Small explorer, with a readable visor and a user-triggered wave.
    const astro = new T.Group(); world.add(astro);
    const suit = material(0xe4eced, { roughness: 0.55, metalness: 0.1 });
    const trim = material(0x273d52); const cyan = new T.MeshBasicMaterial({ color: 0x74ede1 });
    const head = new T.Mesh(new T.SphereGeometry(0.38, 24, 16), suit); head.position.y = 0.57; astro.add(head);
    const visor = new T.Mesh(new T.SphereGeometry(0.315, 24, 12), material(0x164959, { metalness: 0.7, emissive: 0x113e4b, emissiveIntensity: 0.3, roughness: 0.12 }));
    visor.position.set(0, 0.58, 0.16); visor.scale.set(1, 0.75, 0.8); astro.add(visor);
    block(astro, [0.55, 0.65, 0.35], [0, 0, 0], suit);
    block(astro, [0.31, 0.22, 0.04], [0, 0.05, 0.2], trim);
    block(astro, [0.18, 0.025, 0.045], [0, 0.08, 0.23], cyan);
    block(astro, [0.45, 0.55, 0.24], [0, 0.02, -0.28], trim);
    [-1, 1].forEach(sign => {
      block(astro, [0.2, 0.48, 0.24], [sign * 0.18, -0.5, 0], suit);
      block(astro, [0.23, 0.14, 0.32], [sign * 0.18, -0.76, 0.04], trim);
    });
    const leftArm = block(astro, [0.18, 0.5, 0.22], [-0.4, 0.04, 0], suit); leftArm.rotation.z = -0.22;
    const wavingArm = new T.Group(); wavingArm.position.set(0.36, 0.25, 0); astro.add(wavingArm);
    block(wavingArm, [0.18, 0.5, 0.22], [0.08, -0.19, 0], suit); wavingArm.rotation.z = 0.35;
    const flame = new T.Mesh(new T.ConeGeometry(0.16, 0.48, 12), new T.MeshBasicMaterial({ color: 0x72ebde, transparent: true, opacity: 0.65 }));
    flame.rotation.x = Math.PI; flame.position.set(0, -0.5, -0.28); astro.add(flame);
    astro.position.set(-spanX / 2 - 1.5, 2.3, 3.1); astro.rotation.y = 0.35;

    // Planet details come from the public page, never from invented personal facts.
    const textOf=selector=>document.querySelector(selector)?.textContent.trim().replace(/\s+/g,' ') || '';
    const destinations = [
      {title:'Profil',href:'#home',text:textOf('.hero-description'),color:0x67d9d0},
      {title:'Keahlian',href:'#skills',text:Array.from(document.querySelectorAll('#skills .tech-item')).map(el=>el.textContent.trim()).filter(Boolean).slice(0,18).join(' · '),color:0xa296ef},
      {title:'Karya',href:'#featured',text:Array.from(document.querySelectorAll('#featured h3')).map(el=>el.textContent.trim()).join(' · ') || 'Jelajahi proyek yang ditampilkan di portofolio.',color:0xf0c37b},
      {title:'GitHub',href:'https://github.com/RelV-Dev',text:'Repositori, commit, dan aktivitas kontribusi RelV-Dev. Pilih batang atau pemilih tanggal untuk membaca kontribusi harian.',color:0x88e3a3}
    ];
    const navigation=document.createElement('div'); navigation.className='gh-destinations'; navigation.setAttribute('role','group'); navigation.setAttribute('aria-label','Jelajahi semesta Farel');
    const detail=document.createElement('div'); detail.className='gh-destination-detail'; detail.hidden=true;
    const heading=document.createElement('strong'), description=document.createElement('p'), link=document.createElement('a'), close=document.createElement('button');
    close.type='button'; close.textContent='Tutup'; close.setAttribute('aria-label','Tutup informasi planet');
    detail.append(heading,description,link,close);
    container.parentElement.insertBefore(navigation,container); container.parentElement.insertBefore(detail,container.nextSibling);
    let activeDestination=null;
    function openDestination(d) {
      activeDestination=d; heading.textContent=d.title; description.textContent=d.text || 'Informasi tersedia pada bagian ini di website.';
      link.textContent='Jelajahi '+d.title+' ↗'; link.href=d.href; detail.hidden=false;
      navigation.querySelectorAll('[aria-pressed]').forEach(b=>b.setAttribute('aria-pressed',String(b.textContent===d.title)));
    }
    on(close,'click',()=>{detail.hidden=true; navigation.querySelectorAll('[aria-pressed]').forEach(b=>b.setAttribute('aria-pressed','false')); activeDestination?.button.focus();});
    on(link,'click',()=>{detail.hidden=true;});
    destinations.forEach((d,i)=>{
      d.phase=i*Math.PI/2+0.5;
      d.mesh=new T.Mesh(new T.IcosahedronGeometry(0.65,2), material(d.color,{emissive:d.color,emissiveIntensity:0.12,roughness:0.45}));
      d.mesh.position.set(Math.cos(d.phase)*14,2.3,Math.sin(d.phase)*6.7); world.add(d.mesh);
      d.caption=label(d.title.toUpperCase(),d.mesh.position.x,3.45,d.mesh.position.z,'#d2e7ef',2.8);
      const button=document.createElement('button');button.type='button';button.textContent=d.title;button.setAttribute('aria-pressed','false');d.button=button;
      on(button,'click',()=>openDestination(d));navigation.append(button);
    });
    const astroButton=document.createElement('button');astroButton.type='button';astroButton.textContent='Astronaut ✧';navigation.append(astroButton);

    const selection = new T.LineSegments(new T.EdgesGeometry(box), new T.LineBasicMaterial({ color: 0xf0ffd9 })); chart.add(selection);
    const tooltip = document.createElement('div'); tooltip.className = 'gh-tooltip'; tooltip.hidden = true; tooltip.setAttribute('aria-hidden', 'true'); container.appendChild(tooltip);
    const greetingBubble = document.createElement('div'); greetingBubble.className = 'gh-greeting'; greetingBubble.hidden = true;
    greetingBubble.setAttribute('role', 'status'); greetingBubble.textContent = 'Halo dari orbit! Terus berkarya ✧'; container.appendChild(greetingBubble);
    const slider = $('#gh-day-slider'); slider.max = String(days.length - 1);
    $('#gh-date-range').textContent = hasData ? `${shortDate.format(first.stamp)} — ${shortDate.format(last.stamp)}` : 'Menunggu data';
    slider.disabled = !hasData;
    function selectDay(index, showTip = false) {
      if (!hasData) return;
      selected = Math.max(0, Math.min(days.length - 1, index));
      const d = days[selected], p = coordinates[selected];
      slider.value = String(selected); slider.setAttribute('aria-valuetext', `${dateFormat.format(d.stamp)}, ${format.format(d.count)} kontribusi`);
      $('#gh-day-date').textContent = dateFormat.format(d.stamp);
      $('#gh-day-count').textContent = `${format.format(d.count)} kontribusi`;
      selection.position.set(p.x, p.height / 2, p.z); selection.scale.set(stepX * 0.84, p.height + 0.08, stepZ * 0.84);
      tooltip.textContent = `${dateFormat.format(d.stamp)} · ${format.format(d.count)} kontribusi`;
      tooltip.hidden = !showTip; invalidate();
    }
    const languages = $('#gh-languages'); languages.replaceChildren();
    const languageList = Array.isArray(data.topLanguages) ? data.topLanguages.filter(l => l && typeof l.name === 'string').slice(0, 3) : [];
    for (const [i, language] of languageList.entries()) {
      const row = document.createElement('div'); row.className = 'gh-language';
      const dot = document.createElement('i'); dot.style.background = ['#8fe9cc', '#a79ce4', '#67bbc8'][i];
      const name = document.createElement('span'); name.textContent = language.name.slice(0, 40);
      const percent = document.createElement('b'); const value = Number(language.percentage);
      percent.textContent = Number.isFinite(value) && value >= 0 && value <= 100 ? `${format.format(value)}%` : '';
      row.append(dot, name, percent); languages.append(row);
    }
    if (!languageList.length) { const empty = document.createElement('span'); empty.className = 'gh-muted'; empty.textContent = 'Data bahasa belum tersedia'; languages.append(empty); }
    $('.gh-toolbar').hidden = false; $('.gh-inspector').hidden = !hasData;
    const hint = $('#gh-interaction-hint');
    if (matchMedia('(pointer: coarse)').matches) hint.textContent = 'Sentuh batang · gunakan pemilih tanggal';
    const motionButton = $('#gh-motion');
    motionButton.disabled = reduced;
    function updateMotionUI() { motionButton.textContent = reduced ? 'Gerak dikurangi' : paused ? 'Lanjutkan' : 'Jeda'; motionButton.setAttribute('aria-pressed', String(paused)); }
    updateMotionUI();

    function cameraPosition(mode) {
      const aspect = Math.max(0.45, container.clientWidth / Math.max(1, container.clientHeight));
      // Fit the full deck in both orientations, including narrow phones.
      const distance = Math.max(23, (spanX + 5) / (2 * Math.tan(T.MathUtils.degToRad(19)) * aspect));
      return mode === 'top' ? new T.Vector3(0, distance * 1.08, 0.8) : new T.Vector3(distance * 0.15, distance * 0.67, distance * 0.8);
    }
    function setView(mode, immediate = false) {
      view = mode; orbitEnabled = mode === 'orbit'; tooltip.hidden = true;
      controls.target.set(0, 0.7, 0);
      panel.querySelectorAll('[data-gh-view]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.ghView === mode)));
      const target = cameraPosition(mode);
      if (immediate || paused || reduced) { cameraTransition = null; camera.position.copy(target); controls.update(); }
      else cameraTransition = { from: camera.position.clone(), to: target, progress: 0 };
      invalidate();
    }
    function resize() {
      const width = container.clientWidth, height = container.clientHeight;
      if (!width || !height || disposed) return;
      renderer.setSize(width, height); camera.aspect = width / height; camera.updateProjectionMatrix(); setView(view, true);
    }
    const raycaster = new T.Raycaster(), pointer = new T.Vector2();
    let pointerDown = null, dragging = false;
    function pick(event, activate = false) {
      if (dragging || intro < 1.6 || lost) return;
      const rect = canvas.getBoundingClientRect();
      pointer.set(((event.clientX - rect.left) / rect.width) * 2 - 1, -((event.clientY - rect.top) / rect.height) * 2 + 1);
      scene.updateMatrixWorld(true); raycaster.setFromCamera(pointer, camera);
      const target = raycaster.intersectObjects([...destinations.map(d=>d.mesh), astro], true)[0];
      if (target) {
        canvas.style.cursor='pointer';
        if (activate) {
          const destination=destinations.find(d=>d.mesh===target.object);
          if (destination) openDestination(destination); else greet();
        }
        return;
      }
      const hit = hasData ? raycaster.intersectObject(bars)[0] : null;
      if (hit && hit.instanceId !== undefined) selectDay(hit.instanceId, true);
      else tooltip.hidden = true;
      canvas.style.cursor = hit ? 'pointer' : 'grab';
    }
    on(canvas, 'pointerdown', e => { pointerDown = { x: e.clientX, y: e.clientY }; dragging = false; cameraTransition = null; });
    on(canvas, 'pointermove', e => {
      if (pointerDown && Math.hypot(e.clientX - pointerDown.x, e.clientY - pointerDown.y) > 5) dragging = true;
      if (e.pointerType === 'mouse' && !pointerDown) pick(e);
      if (dragging) { tooltip.hidden = true; orbitEnabled = false; }
    });
    on(canvas, 'pointerup', e => { if (!dragging) pick(e, true); pointerDown = null; dragging = false; });
    on(canvas, 'pointercancel', () => { pointerDown = null; dragging = false; });
    on(canvas, 'pointerleave', () => { pointerDown = null; dragging = false; tooltip.hidden = true; });
    on(slider, 'input', () => selectDay(Number(slider.value)));
    on(controls, 'change', () => invalidate());
    panel.querySelectorAll('[data-gh-view]').forEach(button => on(button, 'click', () => setView(button.dataset.ghView)));
    on($('#gh-reset'), 'click', () => setView('orbit'));
    on(motionButton, 'click', () => {
      paused = !paused;
      if (paused) { intro = 2; buildBars(); if (cameraTransition) camera.position.copy(cameraTransition.to); cameraTransition = null; greeting = 0; greetingBubble.hidden = true; controls.update(); }
      updateMotionUI(); invalidate();
    });
    function greet() {
      greeting = 2.6; flight = (flight + 1) % 3; greetingBubble.hidden = false;
      greetingBubble.textContent = ['Halo! Pilih planet untuk mengenal Farel.', 'Siap menjelajah! Lihat planet Karya untuk proyek saya.', 'Selamat datang di orbit GitHub saya!'][flight];
      if (paused || reduced) wavingArm.rotation.z = -2.5;
      invalidate();
    }
    on($('#gh-greet'), 'click', greet);
    on(astroButton, 'click', greet);
    on(motionQuery, 'change', () => {
      reduced = motionQuery.matches; paused = reduced; motionButton.disabled = reduced;
      if (reduced) { intro = 2; buildBars(); cameraTransition = null; }
      updateMotionUI(); invalidate();
    });
    on(document, 'visibilitychange', () => { if (document.hidden) stop(); else invalidate(); });
    on(canvas, 'webglcontextlost', e => { e.preventDefault(); lost = true; stop(); hint.textContent = 'Tampilan 3D dijeda oleh perangkat. Detail tanggal tetap tersedia.'; });
    on(canvas, 'webglcontextrestored', () => { lost = false; hint.textContent = 'Tampilan 3D dipulihkan · pilih batang untuk detail'; invalidate(); });
    const visibility = new IntersectionObserver(entries => {
      visible = entries.some(e => e.isIntersecting); if (visible) invalidate(); else stop();
    }, { threshold: 0.01 }); visibility.observe(container);
    const sizes = new ResizeObserver(resize); sizes.observe(container);
    function stop() { cancelAnimationFrame(frame); frame = 0; lastFrame = 0; }
    function invalidate() { if (!frame && visible && !document.hidden && !lost && !disposed) frame = requestAnimationFrame(tick); }
    function tick(now) {
      frame = 0;
      if (!visible || document.hidden || lost || disposed) return;
      const moving = !paused && !reduced;
      if (moving && lastFrame && now - lastFrame < 30) { invalidate(); return; }
      const dt = lastFrame ? Math.min(0.06, (now - lastFrame) / 1000) : 1 / 30; lastFrame = now;
      if (moving) {
        elapsed += dt;
        if (orbitEnabled && view === 'orbit' && !cameraTransition && !pointerDown) {
          const offset=camera.position.clone().sub(controls.target);
          offset.applyAxisAngle(new T.Vector3(0,1,0),dt*0.055);
          camera.position.copy(controls.target).add(offset); camera.lookAt(controls.target);
        }
        destinations.forEach((d,i)=>{
          const angle=d.phase+elapsed*0.075;
          d.mesh.position.set(Math.cos(angle)*14, 2.3+Math.sin(elapsed+i)*0.25, Math.sin(angle)*6.7);
          d.caption.position.copy(d.mesh.position); d.caption.position.y+=1.15;
          d.mesh.rotation.y+=dt*0.35;
        });
        if (intro < 1.7) { intro += dt; buildBars(); }
        satellite.position.set(Math.cos(elapsed * 0.15) * 14.8, -0.9, Math.sin(elapsed * 0.15) * 7);
        planet.rotation.y = Math.sin(elapsed * 0.12) * 0.12;
        stars.rotation.y = Math.sin(elapsed * 0.015) * 0.018;
        astro.position.y = 2.3 + Math.sin(elapsed * 1.2) * 0.3;
        astro.position.x = -spanX / 2 - 1.5 + Math.sin(elapsed * 0.25)*0.6;
        if (greeting > 0 && flight === 2) astro.rotation.x = Math.sin((2.6-greeting)/2.6*Math.PI)*Math.PI*2;
        else astro.rotation.x = 0;
        astro.rotation.z = Math.sin(elapsed * 0.7) * 0.06;
        flame.scale.y = 1 + Math.sin(elapsed * 7) * 0.15;
        if (greeting > 0) {
          greeting -= dt; wavingArm.rotation.z = -2.5 + Math.sin(elapsed * 10) * 0.3;
          if (greeting <= 0) { greetingBubble.hidden = true; wavingArm.rotation.z = 0.35; }
        }
      }
      if (cameraTransition && moving) {
        cameraTransition.progress = Math.min(1, cameraTransition.progress + dt / 0.8);
        const t = cameraTransition.progress; const ease = t * t * (3 - 2 * t);
        camera.position.lerpVectors(cameraTransition.from, cameraTransition.to, ease); controls.update();
        if (t === 1) cameraTransition = null;
      }
      selection.visible = hasData && intro >= 1.6;
      renderer.render(scene, camera);
      if (moving) invalidate(); else lastFrame = 0;
    }
    function dispose() {
      if (disposed) return;
      disposed = true; stop(); visibility.disconnect(); sizes.disconnect(); cleanups.forEach(fn => fn()); controls.dispose();
      const geometries = new Set(), materials = new Set(), textures = new Set();
      scene.traverse(object => {
        if (object.geometry) geometries.add(object.geometry);
        for (const mat of object.material ? (Array.isArray(object.material) ? object.material : [object.material]) : []) { materials.add(mat); if (mat.map) textures.add(mat.map); }
      });
      textures.forEach(t => t.dispose()); geometries.forEach(g => g.dispose()); materials.forEach(m => m.dispose()); renderer.dispose();
      navigation.remove(); detail.remove();
      delete container._githubScene;
    }
    on(window, 'pagehide', event => { if (event.persisted) stop(); else dispose(); });
    on(window, 'pageshow', () => invalidate());
    resize(); selectDay(selected); satellite.position.set(14.8, -0.9, 0); renderer.render(scene, camera);
    container.dataset.ready = 'true';
    const api = { dispose }; container._githubScene = api; return api;
  }
  window.PortfolioGithubScene = { mount, normalizeDays };
})();
