#!/bin/bash
echo "Setting Vercel environment variables..."

echo "https://renthub-tbj7yxj7.on-forge.com/api" | vercel env add NEXT_PUBLIC_API_URL production
echo "https://renthub-tbj7yxj7.on-forge.com/api/v1" | vercel env add NEXT_PUBLIC_API_BASE_URL production
echo "https://frontend-olyeojynk-madsens-projects.vercel.app" | vercel env add NEXT_PUBLIC_APP_URL production
echo "https://frontend-olyeojynk-madsens-projects.vercel.app" | vercel env add NEXTAUTH_URL production
echo "JJbZoOgDVutqa9ZPrcpxPoNT3PUgONPInumvvo/8UTI=" | vercel env add NEXTAUTH_SECRET production
echo "RentHub" | vercel env add NEXT_PUBLIC_APP_NAME production
echo "production" | vercel env add NEXT_PUBLIC_APP_ENV production

echo "✅ All environment variables set!"
