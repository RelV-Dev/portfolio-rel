-- Replace the placeholder with the existing Supabase Auth user's email.
-- This script fails rather than silently enrolling nobody or guessing an account.
DO $$
DECLARE admin_id uuid;
BEGIN
 SELECT id INTO admin_id FROM auth.users WHERE lower(email)=lower('rel@relv.biz.id');
 IF admin_id IS NULL THEN RAISE EXCEPTION 'Set the existing admin email before running this script'; END IF;
 INSERT INTO public.portfolio_admins(user_id) VALUES(admin_id) ON CONFLICT DO NOTHING;
END $$;
