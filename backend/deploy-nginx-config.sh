#!/bin/bash

# RentHub Nginx Configuration Deployment Script
# Script pentru deploy-ul configurației Nginx cu validare și rollback automat

set -euo pipefail

# Configurare variabile
NGINX_CONFIG_SOURCE="/home/forge/renthub-tbj7yxj7.on-forge.com/nginx-forge-production.conf"
NGINX_CONFIG_TARGET="/etc/nginx/sites-available/renthub-tbj7yxj7.on-forge.com"
NGINX_CONFIG_ENABLED="/etc/nginx/sites-enabled/renthub-tbj7yxj7.on-forge.com"
BACKUP_DIR="/home/forge/backups/nginx"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/nginx_config_backup_${TIMESTAMP}"
LOG_FILE="/home/forge/logs/nginx-deployment.log"

# Culori pentru output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funcții utilitare
log_message() {
    local level=$1
    local message=$2
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${timestamp} [${level}] ${message}" | tee -a "${LOG_FILE}"
}

log_info() {
    log_message "INFO" "$1"
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    log_message "SUCCESS" "$1"
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    log_message "WARNING" "$1"
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    log_message "ERROR" "$1"
    echo -e "${RED}[ERROR]${NC} $1"
}

check_prerequisites() {
    log_info "Verificare prerequisite..."
    
    # Verificăm dacă suntem pe serverul corect
    if [[ $(hostname) != "renthub-tbj7yxj7.on-forge.com" ]]; then
        log_error "Acest script trebuie rulat pe serverul renthub-tbj7yxj7.on-forge.com"
        exit 1
    fi
    
    # Verificăm dacă fișierul config există
    if [[ ! -f "${NGINX_CONFIG_SOURCE}" ]]; then
        log_error "Fișierul configurație sursă nu există: ${NGINX_CONFIG_SOURCE}"
        exit 1
    fi
    
    # Verificăm permisiunile
    if [[ ! -w "/etc/nginx/sites-available" ]]; then
        log_error "Nu avem permisiuni de scriere în /etc/nginx/sites-available"
        exit 1
    fi
    
    log_success "Prerequisite verificate cu succes"
}

create_backup() {
    log_info "Creare backup configurație existentă..."
    
    # Creăm directorul de backup dacă nu există
    mkdir -p "${BACKUP_DIR}"
    
    # Backup pentru configurația existentă
    if [[ -f "${NGINX_CONFIG_TARGET}" ]]; then
        cp "${NGINX_CONFIG_TARGET}" "${BACKUP_FILE}"
        log_success "Backup creat: ${BACKUP_FILE}"
    else
        log_warning "Nu există configurație anterioară pentru backup"
    fi
    
    # Backup pentru logs Nginx
    if [[ -d "/var/log/nginx" ]]; then
        tar -czf "${BACKUP_DIR}/nginx_logs_${TIMESTAMP}.tar.gz" -C /var/log/nginx . 2>/dev/null || true
    fi
}

validate_nginx_config() {
    log_info "Validare sintaxă configurație Nginx..."
    
    # Testăm configurația Nginx
    if nginx -t; then
        log_success "Configurația Nginx este validă"
        return 0
    else
        log_error "Configurația Nginx are erori de sintaxă"
        return 1
    fi
}

deploy_config() {
    log_info "Deploy configurație Nginx..."
    
    # Copiem noua configurație
    cp "${NGINX_CONFIG_SOURCE}" "${NGINX_CONFIG_TARGET}"
    
    # Setăm permisiuni corecte
    chmod 644 "${NGINX_CONFIG_TARGET}"
    chown root:root "${NGINX_CONFIG_TARGET}"
    
    log_success "Configurație copiată cu succes"
}

create_symbolic_link() {
    log_info "Creare link simbolic..."
    
    # Ștergem link-ul existent dacă există
    if [[ -L "${NGINX_CONFIG_ENABLED}" ]]; then
        rm "${NGINX_CONFIG_ENABLED}"
    fi
    
    # Creăm link-ul simbolic
    ln -s "${NGINX_CONFIG_TARGET}" "${NGINX_CONFIG_ENABLED}"
    
    log_success "Link simbolic creat cu succes"
}

test_endpoints() {
    log_info "Testare endpoint-uri critice..."
    
    local endpoints=(
        "https://renthub-tbj7yxj7.on-forge.com/health"
        "https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
        "https://renthub-tbj7yxj7.on-forge.com/api/v1/auth/user"
    )
    
    local failed_tests=0
    
    for endpoint in "${endpoints[@]}"; do
        log_info "Testare: ${endpoint}"
        
        # Așteptăm 5 secunde după reload Nginx
        sleep 2
        
        # Testăm endpoint-ul
        if curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${endpoint}" | grep -q "200\|201\|204"; then
            log_success "✓ ${endpoint} - OK"
        else
            log_error "✗ ${endpoint} - FAILED"
            ((failed_tests++))
        fi
    done
    
    return ${failed_tests}
}

reload_nginx() {
    log_info "Reload serviciu Nginx..."
    
    if systemctl reload nginx; then
        log_success "Nginx reîncărcat cu succes"
        return 0
    else
        log_error "Eroare la reîncărcarea Nginx"
        return 1
    fi
}

rollback() {
    log_warning "Rollback la configurația anterioară..."
    
    if [[ -f "${BACKUP_FILE}" ]]; then
        cp "${BACKUP_FILE}" "${NGINX_CONFIG_TARGET}"
        reload_nginx
        log_success "Rollback efectuat cu succes"
    else
        log_error "Nu există backup pentru rollback"
    fi
}

monitor_logs() {
    log_info "Monitorizare logs pentru 30 secunde..."
    
    timeout 30 tail -f /var/log/nginx/error.log /var/log/nginx/access.log 2>/dev/null || true
}

cleanup() {
    log_info "Curățare fișiere temporare..."
    # Nu ștergem backup-urile, le păstrăm pentru istoric
}

# Funcție principală
main() {
    log_info "=== Începere deployment configurație Nginx ==="
    
    # Verificăm argumentele
    local skip_tests=false
    local force_rollback=false
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            --skip-tests)
                skip_tests=true
                shift
                ;;
            --rollback)
                force_rollback=true
                shift
                ;;
            --help)
                echo "Usage: $0 [--skip-tests] [--rollback] [--help]"
                echo "  --skip-tests    Sari peste testele de endpoint"
                echo "  --rollback      Efectuează rollback la configurația anterioară"
                echo "  --help          Afișează acest mesaj de ajutor"
                exit 0
                ;;
            *)
                log_error "Argument necunoscut: $1"
                exit 1
                ;;
        esac
    done
    
    if [[ "${force_rollback}" == true ]]; then
        rollback
        exit 0
    fi
    
    # Executăm pașii de deployment
    check_prerequisites
    create_backup
    deploy_config
    create_symbolic_link
    
    # Validăm configurația
    if ! validate_nginx_config; then
        log_error "Configurația Nginx nu este validă"
        rollback
        exit 1
    fi
    
    # Reîncărcăm Nginx
    if ! reload_nginx; then
        log_error "Eroare la reîncărcarea Nginx"
        rollback
        exit 1
    fi
    
    # Testăm endpoint-urile (dacă nu este skip)
    if [[ "${skip_tests}" == false ]]; then
        if ! test_endpoints; then
            log_error "Testele de endpoint au eșuat"
            rollback
            exit 1
        fi
    fi
    
    # Monitorizăm logs
    monitor_logs
    
    # Curățare
    cleanup
    
    log_success "=== Deployment finalizat cu succes ==="
    log_info "Configurație deployată: ${NGINX_CONFIG_TARGET}"
    log_info "Backup creat: ${BACKUP_FILE}"
    log_info "Logs: ${LOG_FILE}"
}

# Executăm scriptul
main "$@"