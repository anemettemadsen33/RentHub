# Staging Environment Configuration

environment = "staging"
aws_region  = "us-east-1"

# Domain Configuration
domain_name         = "staging.renthub.com"
acm_certificate_arn = "arn:aws:acm:us-east-1:ACCOUNT_ID:certificate/CERT_ID"

# VPC Configuration
vpc_cidr         = "10.1.0.0/16"
public_subnets   = ["10.1.1.0/24", "10.1.2.0/24", "10.1.3.0/24"]
private_subnets  = ["10.1.11.0/24", "10.1.12.0/24", "10.1.13.0/24"]
database_subnets = ["10.1.21.0/24", "10.1.22.0/24", "10.1.23.0/24"]

# EKS Configuration
kubernetes_version = "1.28"

node_groups = {
  general = {
    instance_types = ["t3.large"]
    min_size      = 2
    max_size      = 10
    desired_size  = 3
    disk_size     = 50
  }
}

# Database Configuration
db_instance_class    = "db.t3.large"
db_allocated_storage = 100
database_name        = "renthub_staging"

# Redis Configuration
redis_node_type  = "cache.t3.medium"
redis_num_nodes  = 2

# Auto Scaling Configuration
autoscaling_min_size     = 2
autoscaling_max_size     = 15
autoscaling_desired_size = 3

# Monitoring Configuration
alert_email = "staging-alerts@renthub.com"
