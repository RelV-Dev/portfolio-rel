-- Create guestbook table
CREATE TABLE IF NOT EXISTS guestbook (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    message VARCHAR(200) NOT NULL,
    is_approved BOOLEAN DEFAULT false NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- Enable Row Level Security (RLS)
ALTER TABLE guestbook ENABLE ROW LEVEL SECURITY;

-- Policy: Allow public (anonymous) users to view only approved guestbook entries
CREATE POLICY "Allow public select approved entries" ON guestbook
    FOR SELECT
    USING (is_approved = true);

-- Policy: Allow public (anonymous) users to submit entries, enforcing that is_approved MUST be false
CREATE POLICY "Allow public insert pending entries" ON guestbook
    FOR INSERT
    WITH CHECK (is_approved = false);

-- Policy: Allow authenticated admin users full CRUD access
CREATE POLICY "Allow authenticated admin full CRUD" ON guestbook
    FOR ALL
    TO authenticated
    USING (true)
    WITH CHECK (true);
