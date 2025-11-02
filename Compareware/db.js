import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm'

const SUPABASE_URL = 'https://nhjzwkjjmjqwphpobkrr.supabase.co'
const SUPABASE_KEY = 'YOUR-ANON-KEY' // Replace with your anon/public key

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY)

export async function getProducts(category) {
    const { data, error } = await supabase
        .from('products')
        .select('*')
        .eq('category', category)
    
    if (error) throw error
    return data
}

export async function getUser() {
    const { data: { user }, error } = await supabase.auth.getUser()
    if (error) throw error
    return user
}

export async function signIn(email, password) {
    const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password
    })
    if (error) throw error
    return data
}

export async function signUp(email, password, username) {
    const { data, error } = await supabase.auth.signUp({
        email,
        password,
        options: {
            data: {
                username
            }
        }
    })
    if (error) throw error
    return data
}

export async function saveComparison(userId, product1Id, product2Id, category) {
    const { data, error } = await supabase
        .from('comparisons')
        .insert([
            {
                user_id: userId,
                product1_id: product1Id,
                product2_id: product2Id,
                category
            }
        ])
    if (error) throw error
    return data
}

