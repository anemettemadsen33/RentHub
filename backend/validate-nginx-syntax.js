#!/usr/bin/env node

/**
 * Nginx Configuration Syntax Validator
 * Validează sintaxa configurației Nginx fără a necesita nginx instalat local
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

class NginxConfigValidator {
    constructor() {
        this.errors = [];
        this.warnings = [];
        this.lines = [];
        this.contextStack = [];
        this.currentLine = 0;
    }

    validate(filePath) {
        console.log(`🔍 Validare configurație Nginx: ${filePath}`);
        
        if (!fs.existsSync(filePath)) {
            this.addError(`Fișierul nu există: ${filePath}`);
            return false;
        }

        const content = fs.readFileSync(filePath, 'utf8');
        this.lines = content.split('\n');
        
        let result = true;
        
        for (let i = 0; i < this.lines.length; i++) {
            this.currentLine = i + 1;
            const line = this.lines[i].trim();
            
            // Ignorăm comentariile și liniile goale
            if (line.startsWith('#') || line === '') {
                continue;
            }
            
            // Verificăm sintaxa pentru fiecare linie
            if (!this.validateLine(line)) {
                result = false;
            }
        }
        
        // Verificăm dacă toate blocurile sunt închise
        if (this.contextStack.length > 0) {
            this.addError(`Blocuri neînchise: ${this.contextStack.join(', ')}`);
            result = false;
        }
        
        return result;
    }

    validateLine(line) {
        // Ignorăm liniile care sunt doar comentarii sau goale
        if (line === '' || line.startsWith('#')) {
            return true;
        }
        
        // Verificăm dacă linia conține doar o acoladă de închidere
        if (line.trim() === '}') {
            return this.validateClosingBrace();
        }
        
        // Verificăm directivele principale de nivel înalt
        if (line.includes('http {')) {
            return this.validateHttpBlock(line);
        }
        
        if (line.includes('events {')) {
            return this.validateEventsBlock(line);
        }
        
        if (line.includes('server {')) {
            return this.validateServerBlock(line);
        }
        
        if (line.includes('location ')) {
            return this.validateLocationBlock(line);
        }
        
        if (line.includes('upstream ')) {
            return this.validateUpstreamBlock(line);
        }
        
        if (line.includes('map ')) {
            return this.validateMapBlock(line);
        }
        
        // Verificăm directivele simple
        return this.validateSimpleDirective(line);
    }

    validateHttpBlock(line) {
        if (!line.startsWith('http {')) {
            this.addError(`Bloc http invalid: ${line}`);
            return false;
        }
        
        this.contextStack.push('http');
        return true;
    }

    validateEventsBlock(line) {
        if (!line.startsWith('events {')) {
            this.addError(`Bloc events invalid: ${line}`);
            return false;
        }
        
        this.contextStack.push('events');
        return true;
    }

    validateServerBlock(line) {
        if (!line.startsWith('server {')) {
            this.addError(`Bloc server invalid: ${line}`);
            return false;
        }
        
        this.contextStack.push('server');
        return true;
    }

    validateLocationBlock(line) {
        const locationRegex = /^location\s+[~*=\^~]*\s*[^\s]+\s*\{/;
        if (!locationRegex.test(line)) {
            this.addError(`Bloc location invalid: ${line}`);
            return false;
        }
        
        this.contextStack.push('location');
        return true;
    }

    validateUpstreamBlock(line) {
        const upstreamRegex = /^upstream\s+\w+\s*\{/;
        if (!upstreamRegex.test(line)) {
            this.addError(`Bloc upstream invalid: ${line}`);
            return false;
        }
        
        this.contextStack.push('upstream');
        return true;
    }

    validateMapBlock(line) {
        const mapRegex = /^map\s+\$[^\s]+\s+\$[^\s]+\s*\{/;
        if (!mapRegex.test(line)) {
            this.addError(`Bloc map invalid: ${line}`);
            return false;
        }
        
        this.contextStack.push('map');
        return true;
    }

    validateClosingBrace() {
        if (this.contextStack.length === 0) {
            this.addError(`Acoladă închisă fără bloc deschis`);
            return false;
        }
        
        this.contextStack.pop();
        return true;
    }

    validateSimpleDirective(line) {
        // Verificăm directivele comune
        const validDirectives = [
            // Standarde Nginx
            'listen', 'server_name', 'root', 'index', 'return', 'try_files',
            'proxy_pass', 'proxy_set_header', 'add_header', 'include',
            'ssl_certificate', 'ssl_certificate_key', 'ssl_protocols',
            'ssl_ciphers', 'ssl_prefer_server_ciphers', 'ssl_session_cache',
            'ssl_session_timeout', 'gzip', 'gzip_types', 'gzip_min_length',
            'gzip_comp_level', 'gzip_vary', 'client_max_body_size',
            'proxy_connect_timeout', 'proxy_send_timeout', 'proxy_read_timeout',
            'send_timeout', 'keepalive_timeout', 'worker_processes',
            'worker_connections', 'pid', 'error_log', 'access_log',
            'log_format', 'set', 'rewrite', 'break', 'last',
            'expires', 'etag', 'charset', 'types', 'default_type',
            
            // SSL moderne
            'ssl_session_tickets', 'ssl_stapling', 'ssl_stapling_verify',
            'server_tokens', 'ssl_dhparam',
            
            // Rate limiting
            'limit_req', 'limit_req_zone', 'limit_req_log_level',
            'limit_conn', 'limit_conn_zone', 'limit_conn_log_level',
            
            // Proxy și FastCGI
            'proxy_http_version', 'proxy_buffering', 'proxy_buffer_size',
            'proxy_buffers', 'proxy_busy_buffers_size', 'proxy_temp_file_write_size',
            'fastcgi_pass', 'fastcgi_index', 'fastcgi_param', 'fastcgi_buffer_size',
            'fastcgi_buffers', 'fastcgi_busy_buffers_size', 'fastcgi_temp_file_write_size',
            'fastcgi_read_timeout', 'fastcgi_send_timeout', 'fastcgi_connect_timeout',
            'fastcgi_buffering',
            
            // Compresie avansată
            'gzip_static', 'gzip_proxied', 'brotli', 'brotli_static',
            'brotli_comp_level', 'brotli_types',
            
            // Securitate și access
            'deny', 'allow', 'auth_basic', 'auth_basic_user_file',
            'satisfy', 'log_not_found',
            
            // HTTP/2 și WebSocket
            'http2_push_preload', 'grpc_pass', 'grpc_set_header',
            'uwsgi_pass', 'uwsgi_param', 'scgi_pass', 'scgi_param'
        ];
        
        // Verificăm directivele speciale care au sintaxă complexă
        if (line.startsWith('if ')) {
            return this.validateIfBlock(line);
        }
        
        if (line.includes('limit_req') && line.includes('zone=')) {
            return this.validateLimitReq(line);
        }
        
        if (line.includes('limit_req_zone') || line.includes('limit_conn_zone')) {
            return this.validateLimitZone(line);
        }
        
        if (line.includes('log_format')) {
            return this.validateLogFormat(line);
        }
        
        const parts = line.split(/\s+/);
        const directive = parts[0];
        
        if (line.includes('=') && !line.includes('==')) {
            // Verificăm atribuirile
            return this.validateAssignment(line);
        }
        
        if (validDirectives.includes(directive)) {
            return this.validateDirectiveSyntax(directive, parts.slice(1));
        }
        
        // Verificăm dacă suntem într-un bloc de tip MIME types
        if (this.contextStack.includes('types')) {
            return true; // În blocul types, orice este valid
        }
        
        // Dacă nu recunoaștem directiva, avertizăm dar nu blocăm
        this.addWarning(`Directivă necunoscută: ${directive}`);
        return true;
    }

    validateAssignment(line) {
        const assignmentRegex = /^\$\w+\s*=\s*[^;]+;$/;
        if (!assignmentRegex.test(line)) {
            this.addWarning(`Sintaxă de atribuire neobișnuită: ${line}`);
        }
        return true;
    }

    validateDirectiveSyntax(directive, args) {
        switch (directive) {
            case 'listen':
                return this.validateListen(args);
            case 'server_name':
                return this.validateServerName(args);
            case 'return':
                return this.validateReturn(args);
            case 'proxy_pass':
                return this.validateProxyPass(args);
            case 'add_header':
                return this.validateAddHeader(args);
            default:
                return true;
        }
    }

    validateListen(args) {
        if (args.length === 0) {
            this.addError(`Directiva 'listen' necesită argumente`);
            return false;
        }
        
        const listenArg = args.join(' ');
        const listenRegex = /^(\d+|\d+\.\d+\.\d+\.\d+:\d+|\[::\]:\d+)(\s+ssl)?(\s+http2)?(\s+default_server)?$/;
        
        if (!listenRegex.test(listenArg)) {
            this.addWarning(`Format 'listen' neobișnuit: ${listenArg}`);
        }
        
        return true;
    }

    validateServerName(args) {
        if (args.length === 0) {
            this.addError(`Directiva 'server_name' necesită argumente`);
            return false;
        }
        
        return true;
    }

    validateReturn(args) {
        if (args.length === 0) {
            this.addError(`Directiva 'return' necesită cod status`);
            return false;
        }
        
        // Extragem codul status care poate fi urmat de ';'
        const statusCode = args[0].replace(/;$/, '');
        if (!/^\d{3}$/.test(statusCode)) {
            this.addError(`Cod status invalid pentru 'return': ${statusCode}`);
            return false;
        }
        
        // Pentru codurile 204, 444 etc., nu este necesar al doilea argument
        if (args.length === 1 && ['204', '444'].includes(statusCode)) {
            return true;
        }
        
        // Pentru alte coduri, poate fi necesar un URL sau mesaj
        return true;
    }

    validateProxyPass(args) {
        if (args.length === 0) {
            this.addError(`Directiva 'proxy_pass' necesită URL`);
            return false;
        }
        
        const url = args[0];
        if (!/^https?:\/\/[^\s]+$/.test(url) && !/^https?:\/\/\/[^\s]+$/.test(url)) {
            this.addWarning(`URL 'proxy_pass' neobișnuit: ${url}`);
        }
        
        return true;
    }

    validateAddHeader(args) {
        if (args.length < 2) {
            this.addError(`Directiva 'add_header' necesită nume și valoare`);
            return false;
        }
        
        return true;
    }

    validateIfBlock(line) {
        // Directiva 'if' are sintaxă specială și este validă în Nginx
        const ifRegex = /^if\s+\(.+\)\s*\{$/;
        if (!ifRegex.test(line)) {
            this.addWarning(`Sintaxă 'if' neobișnuită: ${line}`);
        }
        
        this.contextStack.push('if');
        return true;
    }

    validateLimitReq(line) {
        // limit_req zone=api burst=30 nodelay;
        const limitReqRegex = /^limit_req\s+zone=\w+(\s+burst=\d+)?(\s+nodelay)?(\s+delay=\d+)?;$/;
        if (!limitReqRegex.test(line)) {
            this.addWarning(`Sintaxă 'limit_req' neobișnuită: ${line}`);
        }
        return true;
    }

    validateLimitZone(line) {
        // limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
        const limitZoneRegex = /^(limit_req_zone|limit_conn_zone)\s+\$\w+\s+zone=\w+:\d+m\s+rate=\d+r\/[sm];$/;
        if (!limitZoneRegex.test(line)) {
            this.addWarning(`Sintaxă 'limit_zone' neobișnuită: ${line}`);
        }
        return true;
    }

    validateLogFormat(line) {
        // log_format combined '...';
        const logFormatRegex = /^log_format\s+\w+\s+['"].*['"];$/;
        if (!logFormatRegex.test(line)) {
            this.addWarning(`Sintaxă 'log_format' neobișnuită: ${line}`);
        }
        return true;
    }

    addError(message) {
        this.errors.push({
            line: this.currentLine,
            message: message,
            content: this.lines[this.currentLine - 1] || ''
        });
    }

    addWarning(message) {
        this.warnings.push({
            line: this.currentLine,
            message: message,
            content: this.lines[this.currentLine - 1] || ''
        });
    }

    getReport() {
        let report = '\n' + '='.repeat(60) + '\n';
        report += 'RAPORT VALIDARE CONFIGURAȚIE NGINX\n';
        report += '='.repeat(60) + '\n\n';
        
        if (this.errors.length === 0 && this.warnings.length === 0) {
            report += '✅ Configurația este VALIDĂ!\n';
            report += 'Nu au fost găsite erori sau avertismente.\n';
        } else {
            if (this.errors.length > 0) {
                report += '❌ Erori găsite (' + this.errors.length + '):\n';
                report += '-'.repeat(40) + '\n';
                this.errors.forEach(error => {
                    report += `Linia ${error.line}: ${error.message}\n`;
                    report += `  ${error.content.trim()}\n\n`;
                });
            }
            
            if (this.warnings.length > 0) {
                report += '⚠️  Avertismente (' + this.warnings.length + '):\n';
                report += '-'.repeat(40) + '\n';
                this.warnings.forEach(warning => {
                    report += `Linia ${warning.line}: ${warning.message}\n`;
                    report += `  ${warning.content.trim()}\n\n`;
                });
            }
        }
        
        report += '\n' + '='.repeat(60) + '\n';
        return report;
    }
}

// Funcție principală
function main() {
    const configFile = process.argv[2] || 'nginx-forge-production.conf';
    const fullPath = path.resolve(configFile);
    
    console.log(`🚀 Pornire validare configurație Nginx...`);
    
    const validator = new NginxConfigValidator();
    const isValid = validator.validate(fullPath);
    const report = validator.getReport();
    
    console.log(report);
    
    if (!isValid) {
        console.log('❌ Configurația are erori critice și necesită corectare.');
        process.exit(1);
    } else if (validator.warnings.length > 0) {
        console.log('⚠️  Configurația este validă dar are avertismente.');
        process.exit(0);
    } else {
        console.log('✅ Configurația este perfect validă!');
        process.exit(0);
    }
}

// Rulăm validarea
const __filename = fileURLToPath(import.meta.url);
if (process.argv[1] === __filename) {
    main();
}

export default NginxConfigValidator;