const {PGlite}=require('@electric-sql/pglite');
const fs=require('node:fs');const assert=require('node:assert/strict');
(async()=>{
 const db=new PGlite();
 await db.exec(`CREATE ROLE anon; CREATE ROLE authenticated; CREATE ROLE service_role; CREATE SCHEMA auth; CREATE TABLE auth.users(id uuid primary key,email text); CREATE FUNCTION auth.uid() RETURNS uuid LANGUAGE sql AS $$ SELECT NULLIF(current_setting('request.jwt.claim.sub',true),'')::uuid $$; GRANT USAGE ON SCHEMA public,auth TO anon,authenticated,service_role; GRANT EXECUTE ON FUNCTION auth.uid() TO anon,authenticated;`);
 for(const f of ['schema.sql','guestbook.sql','gallery_schema.sql','migrations/001_public_access.sql'])await db.exec(fs.readFileSync(f,'utf8'));
 // Reapplying migration must also work.
 await db.exec(fs.readFileSync('migrations/001_public_access.sql','utf8'));
 await db.exec(`INSERT INTO auth.users VALUES ('00000000-0000-0000-0000-000000000001','admin@test.invalid'),('00000000-0000-0000-0000-000000000002','visitor@test.invalid'); INSERT INTO public.portfolio_admins VALUES ('00000000-0000-0000-0000-000000000001'); INSERT INTO public.testimonials(customer_name,project_title,category,proof_type,transfer_proof,chat_screenshot,project_proof) VALUES ('Customer','Project','Web','image','private-transfer','private-chat','private-project');`);
 await db.exec('SET ROLE anon');
 let publicRows=await db.query('SELECT * FROM public.public_testimonials');assert.equal(publicRows.rows[0].project_proof,null);assert.ok(!('transfer_proof' in publicRows.rows[0]));
 for(const sql of ["SELECT * FROM public.testimonials","INSERT INTO public.guestbook(name,message) VALUES('spam','spam')","SELECT public.consume_public_rate_limit(repeat('a',64),1,60)"]){await assert.rejects(db.exec(sql));}
 await db.exec("RESET ROLE; SET ROLE authenticated; SET request.jwt.claim.sub='00000000-0000-0000-0000-000000000002';");
 assert.equal((await db.query('SELECT public.is_portfolio_admin() AS ok')).rows[0].ok,false);
 assert.equal((await db.query('SELECT * FROM public.testimonials')).rows.length,0);
 await assert.rejects(db.exec("INSERT INTO public.testimonials(customer_name,project_title,category,proof_type) VALUES ('bad','bad','bad','bad')"));
 await db.exec("SET request.jwt.claim.sub='00000000-0000-0000-0000-000000000001';");assert.equal((await db.query('SELECT public.is_portfolio_admin() AS ok')).rows[0].ok,true);
 assert.equal((await db.query('SELECT * FROM public.testimonials')).rows.length,1);
 await db.exec('UPDATE public.testimonials SET public_proof_approved=true');
 assert.equal((await db.query('SELECT project_proof FROM public.public_testimonials')).rows[0].project_proof,'private-project');
 await db.exec('RESET ROLE; SET ROLE service_role');
 assert.equal((await db.query("SELECT public.consume_public_rate_limit(repeat('a',64),1,60) AS ok")).rows[0].ok,true);
 assert.equal((await db.query("SELECT public.consume_public_rate_limit(repeat('a',64),1,60) AS ok")).rows[0].ok,false);
 console.log('PASS idempotent migration, anon/visitor/admin permissions, private proof isolation and shared rate limiter');await db.close();
})().catch(e=>{console.error(e.message);process.exit(1)});
