#!/bin/bash
# ====================================
# Secret Rotation Script - RentHub Production
# ====================================
# Execute this script to rotate compromised or expired secrets
# IMPORTANT: Run this script in a secure environment only!

set -e

echo "🔐 RentHub Secret Rotation Utility"
echo "===================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to rotate SendGrid API Key
rotate_sendgrid() {
    echo -e "${YELLOW}📧 Rotating SendGrid API Key${NC}"
    echo "--------------------------------------"
    echo "1. Login to SendGrid: https://app.sendgrid.com/"
    echo "2. Navigate to: Settings → API Keys"
    echo "3. Click 'Create API Key'"
    echo "4. Name: 'RentHub Production - $(date +%Y-%m-%d)'"
    echo "5. Permissions: Mail Send (Full Access)"
    echo "6. Copy the new key (shown only once!)"
    echo ""
    read -p "Paste the new SendGrid API key: " NEW_SENDGRID_KEY
    
    if [ -z "$NEW_SENDGRID_KEY" ]; then
        echo -e "${RED}❌ No key provided. Skipping SendGrid rotation.${NC}"
        return 1
    fi
    
    echo "MAIL_PASSWORD=$NEW_SENDGRID_KEY" >> .env.new
    echo -e "${GREEN}✅ New SendGrid key added to .env.new${NC}"
    echo -e "${YELLOW}⚠️  Remember to revoke old key: SG.4p9fVE7...${NC}"
    echo ""
}

# Function to rotate AWS credentials
rotate_aws() {
    echo -e "${YELLOW}☁️  Rotating AWS Credentials${NC}"
    echo "--------------------------------------"
    echo "1. Login to AWS Console: https://console.aws.amazon.com/"
    echo "2. Navigate to: IAM → Users → renthub-production-s3"
    echo "3. Security credentials → Create access key"
    echo "4. Use case: Application running outside AWS"
    echo "5. Copy Access Key ID and Secret Access Key"
    echo ""
    read -p "Paste AWS Access Key ID: " NEW_AWS_KEY_ID
    read -p "Paste AWS Secret Access Key: " NEW_AWS_SECRET
    
    if [ -z "$NEW_AWS_KEY_ID" ] || [ -z "$NEW_AWS_SECRET" ]; then
        echo -e "${RED}❌ Incomplete AWS credentials. Skipping AWS rotation.${NC}"
        return 1
    fi
    
    echo "AWS_ACCESS_KEY_ID=$NEW_AWS_KEY_ID" >> .env.new
    echo "AWS_SECRET_ACCESS_KEY=$NEW_AWS_SECRET" >> .env.new
    echo -e "${GREEN}✅ New AWS credentials added to .env.new${NC}"
    echo ""
}

# Function to rotate Stripe keys
rotate_stripe() {
    echo -e "${YELLOW}💳 Rotating Stripe Keys${NC}"
    echo "--------------------------------------"
    echo "1. Login to Stripe: https://dashboard.stripe.com/"
    echo "2. Navigate to: Developers → API keys"
    echo "3. Roll keys under 'Standard keys' section"
    echo "4. Copy new Publishable and Secret keys"
    echo ""
    read -p "Paste Stripe Publishable Key (pk_live_...): " NEW_STRIPE_PK
    read -p "Paste Stripe Secret Key (sk_live_...): " NEW_STRIPE_SK
    
    if [ -z "$NEW_STRIPE_PK" ] || [ -z "$NEW_STRIPE_SK" ]; then
        echo -e "${RED}❌ Incomplete Stripe keys. Skipping Stripe rotation.${NC}"
        return 1
    fi
    
    echo "STRIPE_KEY=$NEW_STRIPE_PK" >> .env.new
    echo "STRIPE_SECRET=$NEW_STRIPE_SK" >> .env.new
    echo -e "${GREEN}✅ New Stripe keys added to .env.new${NC}"
    echo ""
}

# Function to rotate database password
rotate_database() {
    echo -e "${YELLOW}🗄️  Rotating Database Password${NC}"
    echo "--------------------------------------"
    echo "⚠️  WARNING: This will update PostgreSQL password"
    echo ""
    read -p "Generate new password? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Skipping database rotation."
        return 1
    fi
    
    # Generate strong password
    NEW_DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-32)
    
    echo "DB_PASSWORD=$NEW_DB_PASSWORD" >> .env.new
    echo -e "${GREEN}✅ New database password generated${NC}"
    echo -e "${YELLOW}⚠️  CRITICAL: Update PostgreSQL password manually:${NC}"
    echo "   sudo -u postgres psql"
    echo "   ALTER USER forge WITH PASSWORD '$NEW_DB_PASSWORD';"
    echo ""
}

# Function to rotate Redis password
rotate_redis() {
    echo -e "${YELLOW}🔴 Rotating Redis Password${NC}"
    echo "--------------------------------------"
    
    NEW_REDIS_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-32)
    
    echo "REDIS_PASSWORD=$NEW_REDIS_PASSWORD" >> .env.new
    echo -e "${GREEN}✅ New Redis password generated${NC}"
    echo -e "${YELLOW}⚠️  Update Redis config manually:${NC}"
    echo "   sudo nano /etc/redis/redis.conf"
    echo "   requirepass $NEW_REDIS_PASSWORD"
    echo "   sudo systemctl restart redis"
    echo ""
}

# Function to rotate Meilisearch master key
rotate_meilisearch() {
    echo -e "${YELLOW}🔍 Rotating Meilisearch Master Key${NC}"
    echo "--------------------------------------"
    
    NEW_MEILISEARCH_KEY=$(openssl rand -base64 48)
    
    echo "MEILISEARCH_KEY=$NEW_MEILISEARCH_KEY" >> .env.new
    echo -e "${GREEN}✅ New Meilisearch key generated${NC}"
    echo -e "${YELLOW}⚠️  Update Meilisearch startup:${NC}"
    echo "   Update --master-key flag in supervisor/systemd config"
    echo "   Restart Meilisearch service"
    echo ""
}

# Main menu
main() {
    echo "Select secrets to rotate:"
    echo "1) SendGrid API Key (🔴 LEAKED - CRITICAL)"
    echo "2) AWS S3 Credentials"
    echo "3) Stripe Keys"
    echo "4) Database Password"
    echo "5) Redis Password"
    echo "6) Meilisearch Master Key"
    echo "7) Rotate ALL (recommended quarterly)"
    echo "0) Exit"
    echo ""
    read -p "Enter choice [0-7]: " choice
    
    # Create new env file
    rm -f .env.new
    touch .env.new
    
    case $choice in
        1) rotate_sendgrid ;;
        2) rotate_aws ;;
        3) rotate_stripe ;;
        4) rotate_database ;;
        5) rotate_redis ;;
        6) rotate_meilisearch ;;
        7)
            rotate_sendgrid
            rotate_aws
            rotate_stripe
            rotate_database
            rotate_redis
            rotate_meilisearch
            ;;
        0) echo "Exiting."; exit 0 ;;
        *) echo -e "${RED}Invalid choice${NC}"; exit 1 ;;
    esac
    
    # Summary
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}✅ Secret Rotation Complete!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo "📋 Next Steps:"
    echo "1. Review .env.new file"
    echo "2. Backup current .env: cp .env .env.backup.$(date +%Y%m%d)"
    echo "3. Merge changes: cat .env.new >> .env"
    echo "4. Update Forge Environment Variables UI"
    echo "5. Restart services: php artisan config:clear && php artisan queue:restart"
    echo "6. Test all integrations"
    echo "7. Revoke old secrets from service dashboards"
    echo "8. Delete .env.new: rm .env.new"
    echo ""
    echo -e "${YELLOW}⚠️  Security reminder:${NC}"
    echo "   - Keep .env.new secure (contains sensitive data)"
    echo "   - Verify all services working before revoking old secrets"
    echo "   - Update monitoring alerts if using secret-based auth"
    echo ""
}

# Run main function
main
