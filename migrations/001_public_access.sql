-- Apply after schema.sql, gallery_schema.sql and guestbook.sql in Supabase SQL Editor.
-- Enrol the intended admin with migrations/002_enrol_admin.sql BEFORE deploying the new UI.
BEGIN;
CREATE TABLE IF NOT EXISTS public.portfolio_admins (user_id uuid PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE);
ALTER TABLE public.portfolio_admins ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON public.portfolio_admins FROM anon, authenticated;
CREATE OR REPLACE FUNCTION public.is_portfolio_admin() RETURNS boolean
LANGUAGE sql STABLE SECURITY DEFINER SET search_path = '' AS $$
 SELECT EXISTS (SELECT 1 FROM public.portfolio_admins WHERE user_id = (SELECT auth.uid()));
$$;
REVOKE ALL ON FUNCTION public.is_portfolio_admin() FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.is_portfolio_admin() TO anon, authenticated;
-- Replace all legacy permissive policies on these app-owned tables.
DO $$ DECLARE p record; BEGIN
 FOR p IN SELECT schemaname, tablename, policyname FROM pg_policies WHERE schemaname='public' AND tablename IN ('testimonials','portfolio_gallery','guestbook') LOOP
  EXECUTE format('DROP POLICY %I ON %I.%I',p.policyname,p.schemaname,p.tablename);
 END LOOP;
END $$;
CREATE POLICY admin_testimonials ON public.testimonials FOR ALL TO authenticated USING (public.is_portfolio_admin()) WITH CHECK (public.is_portfolio_admin());
CREATE POLICY admin_gallery ON public.portfolio_gallery FOR ALL TO authenticated USING (public.is_portfolio_admin()) WITH CHECK (public.is_portfolio_admin());
CREATE POLICY admin_guestbook ON public.guestbook FOR ALL TO authenticated USING (public.is_portfolio_admin()) WITH CHECK (public.is_portfolio_admin());
CREATE POLICY read_gallery ON public.portfolio_gallery FOR SELECT TO anon, authenticated USING (is_visible = true);
CREATE POLICY read_guestbook ON public.guestbook FOR SELECT TO anon, authenticated USING (is_approved = true);
REVOKE ALL ON public.testimonials FROM anon;
REVOKE INSERT, UPDATE, DELETE ON public.guestbook, public.portfolio_gallery FROM anon;
GRANT SELECT ON public.guestbook, public.portfolio_gallery TO anon, authenticated;
GRANT INSERT ON public.guestbook TO service_role;
GRANT SELECT, INSERT, UPDATE, DELETE ON public.testimonials, public.guestbook, public.portfolio_gallery TO authenticated;
ALTER TABLE public.testimonials ADD COLUMN IF NOT EXISTS public_proof_approved boolean NOT NULL DEFAULT false;
-- Deliberate owner-executed view: only these reviewed fields cross the privacy boundary.
CREATE OR REPLACE VIEW public.public_testimonials WITH (security_barrier=true, security_invoker=false) AS
 SELECT id, customer_name, project_title, category, project_link, demo_video_url,
 CASE WHEN public_proof_approved THEN project_proof ELSE NULL END AS project_proof
 FROM public.testimonials WHERE is_visible = true;
REVOKE ALL ON public.public_testimonials FROM anon, authenticated;
GRANT SELECT ON public.public_testimonials TO anon, authenticated;
CREATE TABLE IF NOT EXISTS public.public_rate_limits (key text PRIMARY KEY, started_at timestamptz NOT NULL, requests integer NOT NULL);
ALTER TABLE public.public_rate_limits ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON public.public_rate_limits FROM anon, authenticated;
CREATE OR REPLACE FUNCTION public.consume_public_rate_limit(bucket_key text, max_requests integer, window_seconds integer)
RETURNS boolean LANGUAGE plpgsql SECURITY DEFINER SET search_path = '' AS $$
DECLARE n integer; BEGIN
 IF max_requests < 1 OR window_seconds < 1 OR length(bucket_key) <> 64 THEN RETURN false; END IF;
 INSERT INTO public.public_rate_limits AS r (key,started_at,requests) VALUES (bucket_key,clock_timestamp(),1)
 ON CONFLICT (key) DO UPDATE SET
 requests=CASE WHEN r.started_at <= clock_timestamp()-make_interval(secs=>window_seconds) THEN 1 ELSE r.requests+1 END,
 started_at=CASE WHEN r.started_at <= clock_timestamp()-make_interval(secs=>window_seconds) THEN clock_timestamp() ELSE r.started_at END
 RETURNING requests INTO n;
 DELETE FROM public.public_rate_limits WHERE started_at < clock_timestamp()-interval '1 day';
 RETURN n <= max_requests;
END $$;
REVOKE ALL ON FUNCTION public.consume_public_rate_limit(text,integer,integer) FROM PUBLIC, anon, authenticated;
GRANT EXECUTE ON FUNCTION public.consume_public_rate_limit(text,integer,integer) TO service_role;
COMMIT;
