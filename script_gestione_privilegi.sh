#!/bin/bash

###############################################################################
# script_gestione_privilegi.sh
# 
# Sistema di gestione privilegi bash per il progetto Musicare
# Permette di gestire: ruoli, privilegi, assegnazioni e verifiche
#
# Autore: Sistema Musicare
# Data: 2025
###############################################################################

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurazione database
DB_HOST="127.0.0.1"
DB_USER="utente_phpmyadmin"
DB_PASS="ringraziandoPENNETTA"
DB_NAME="my_serranojonathan"

###############################################################################
# FUNZIONI DI UTILITÀ
###############################################################################

# Funzione per stampare intestazione
print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

# Funzione per stampare successo
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# Funzione per stampare errore
print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Funzione per stampare info
print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Esegui query MySQL
run_query() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$1"
}

# Esegui query MySQL e ritorna il risultato
get_query_result() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sN -e "$1"
}

###############################################################################
# GESTIONE RUOLI
###############################################################################

# Visualizza tutti i ruoli
show_roles() {
    print_header "VISUALIZZA RUOLI"
    run_query "SELECT id_ruolo, nome_ruolo FROM ruoli;"
    print_success "Ruoli visualizzati"
}

# Aggiungi nuovo ruolo
add_role() {
    read -p "Nome del nuovo ruolo: " ruolo_name
    
    # Verifica se il ruolo esiste già
    existing=$(get_query_result "SELECT COUNT(*) FROM ruoli WHERE nome_ruolo='$ruolo_name';")
    
    if [ "$existing" -gt 0 ]; then
        print_error "Il ruolo '$ruolo_name' esiste già!"
        return 1
    fi
    
    run_query "INSERT INTO ruoli (nome_ruolo) VALUES ('$ruolo_name');"
    print_success "Ruolo '$ruolo_name' aggiunto con successo!"
}

###############################################################################
# GESTIONE PRIVILEGI
###############################################################################

# Visualizza tutti i privilegi
show_privileges() {
    print_header "VISUALIZZA PRIVILEGI"
    run_query "SELECT id_privilegio, nome_privilegio, descrizione FROM privilegi;"
    print_success "Privilegi visualizzati"
}

# Aggiungi nuovo privilegio
add_privilege() {
    read -p "Nome del nuovo privilegio: " priv_name
    read -p "Descrizione del privilegio: " priv_desc
    
    # Verifica se il privilegio esiste già
    existing=$(get_query_result "SELECT COUNT(*) FROM privilegi WHERE nome_privilegio='$priv_name';")
    
    if [ "$existing" -gt 0 ]; then
        print_error "Il privilegio '$priv_name' esiste già!"
        return 1
    fi
    
    run_query "INSERT INTO privilegi (nome_privilegio, descrizione) VALUES ('$priv_name', '$priv_desc');"
    print_success "Privilegio '$priv_name' aggiunto con successo!"
}

###############################################################################
# GESTIONE ASSEGNAZIONI RUOLO-PRIVILEGIO
###############################################################################

# Visualizza assegnazioni ruolo-privilegio
show_role_privileges() {
    print_header "ASSEGNAZIONI RUOLO-PRIVILEGIO"
    run_query "
        SELECT r.nome_ruolo, p.nome_privilegio, p.descrizione
        FROM ruolo_privilegi rp
        INNER JOIN ruoli r ON rp.id_ruolo = r.id_ruolo
        INNER JOIN privilegi p ON rp.id_privilegio = p.id_privilegio
        ORDER BY r.nome_ruolo, p.nome_privilegio;
    "
    print_success "Assegnazioni visualizzate"
}

# Assegna privilegio a ruolo
assign_privilege_to_role() {
    print_info "Ruoli disponibili:"
    run_query "SELECT id_ruolo, nome_ruolo FROM ruoli;"
    read -p "ID del ruolo: " role_id
    
    print_info "Privilegi disponibili:"
    run_query "SELECT id_privilegio, nome_privilegio FROM privilegi;"
    read -p "ID del privilegio: " priv_id
    
    # Verifica se l'assegnazione esiste già
    existing=$(get_query_result "
        SELECT COUNT(*) FROM ruolo_privilegi 
        WHERE id_ruolo=$role_id AND id_privilegio=$priv_id;
    ")
    
    if [ "$existing" -gt 0 ]; then
        print_error "Questa assegnazione esiste già!"
        return 1
    fi
    
    run_query "INSERT INTO ruolo_privilegi (id_ruolo, id_privilegio) VALUES ($role_id, $priv_id);"
    print_success "Privilegio assegnato al ruolo con successo!"
}

# Rimuovi privilegio da ruolo
remove_privilege_from_role() {
    show_role_privileges
    
    read -p "ID del ruolo: " role_id
    read -p "ID del privilegio: " priv_id
    
    # Verifica se l'assegnazione esiste
    existing=$(get_query_result "
        SELECT COUNT(*) FROM ruolo_privilegi 
        WHERE id_ruolo=$role_id AND id_privilegio=$priv_id;
    ")
    
    if [ "$existing" -eq 0 ]; then
        print_error "Assegnazione non trovata!"
        return 1
    fi
    
    run_query "DELETE FROM ruolo_privilegi WHERE id_ruolo=$role_id AND id_privilegio=$priv_id;"
    print_success "Privilegio rimosso dal ruolo con successo!"
}

###############################################################################
# VERIFICA PRIVILEGI UTENTE
###############################################################################

# Visualizza privilegi di un utente
show_user_privileges() {
    read -p "ID dell'utente: " user_id
    
    print_header "PRIVILEGI DELL'UTENTE ID: $user_id"
    
    run_query "
        SELECT u.nome, u.cognome, r.nome_ruolo, p.nome_privilegio
        FROM utenti u
        INNER JOIN ruoli r ON u.ruolo = r.nome_ruolo
        LEFT JOIN ruolo_privilegi rp ON r.id_ruolo = rp.id_ruolo
        LEFT JOIN privilegi p ON rp.id_privilegio = p.id_privilegio
        WHERE u.id_utente = $user_id
        ORDER BY p.nome_privilegio;
    "
}

# Verifica se un utente ha un privilegio specifico
verify_user_privilege() {
    read -p "ID dell'utente: " user_id
    read -p "Nome del privilegio: " priv_name
    
    result=$(get_query_result "
        SELECT COUNT(*)
        FROM utenti u
        INNER JOIN ruoli r ON u.ruolo = r.nome_ruolo
        INNER JOIN ruolo_privilegi rp ON r.id_ruolo = rp.id_ruolo
        INNER JOIN privilegi p ON rp.id_privilegio = p.id_privilegio
        WHERE u.id_utente = $user_id AND p.nome_privilegio = '$priv_name';
    ")
    
    if [ "$result" -gt 0 ]; then
        print_success "L'utente HA il privilegio '$priv_name'"
    else
        print_error "L'utente NON HA il privilegio '$priv_name'"
    fi
}

###############################################################################
# STATISTICHE E REPORT
###############################################################################

# Mostra statistiche generali
show_statistics() {
    print_header "STATISTICHE SISTEMA PRIVILEGI"
    
    echo -e "${YELLOW}Numero totali:${NC}"
    run_query "
        SELECT 
            (SELECT COUNT(*) FROM ruoli) AS totale_ruoli,
            (SELECT COUNT(*) FROM privilegi) AS totale_privilegi,
            (SELECT COUNT(*) FROM utenti) AS totale_utenti,
            (SELECT COUNT(*) FROM ruolo_privilegi) AS totale_assegnazioni;
    "
    
    echo -e "\n${YELLOW}Utenti per ruolo:${NC}"
    run_query "
        SELECT ruolo, COUNT(*) as numero_utenti
        FROM utenti
        GROUP BY ruolo;
    "
    
    echo -e "\n${YELLOW}Privilegi per ruolo:${NC}"
    run_query "
        SELECT r.nome_ruolo, COUNT(rp.id_privilegio) as numero_privilegi
        FROM ruoli r
        LEFT JOIN ruolo_privilegi rp ON r.id_ruolo = rp.id_ruolo
        GROUP BY r.id_ruolo, r.nome_ruolo;
    "
}

###############################################################################
# ESPORTAZIONE E BACKUP
###############################################################################

# Esporta privilegi in file
export_privileges() {
    filename="privilegi_backup_$(date +%Y%m%d_%H%M%S).sql"
    
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        privilegi ruoli ruolo_privilegi > "$filename"
    
    print_success "Privilegi esportati in: $filename"
}

###############################################################################
# MENU PRINCIPALE
###############################################################################

show_menu() {
    print_header "GESTIONE PRIVILEGI - MUSICARE"
    echo ""
    echo "  RUOLI:"
    echo "    1) Visualizza ruoli"
    echo "    2) Aggiungi nuovo ruolo"
    echo ""
    echo "  PRIVILEGI:"
    echo "    3) Visualizza privilegi"
    echo "    4) Aggiungi nuovo privilegio"
    echo ""
    echo "  ASSEGNAZIONI:"
    echo "    5) Visualizza assegnazioni ruolo-privilegio"
    echo "    6) Assegna privilegio a ruolo"
    echo "    7) Rimuovi privilegio da ruolo"
    echo ""
    echo "  UTENTI:"
    echo "    8) Visualizza privilegi di un utente"
    echo "    9) Verifica privilegio specifico per utente"
    echo ""
    echo "  AMMINISTRAZIONE:"
    echo "    10) Visualizza statistiche"
    echo "    11) Esporta privilegi"
    echo ""
    echo "    0) Esci"
    echo ""
}

main() {
    # Verifica se mysql è disponibile
    if ! command -v mysql &> /dev/null; then
        print_error "MySQL client non trovato. Installa mysql-client:"
        echo "  sudo apt-get install mysql-client"
        exit 1
    fi
    
    while true; do
        show_menu
        read -p "Seleziona un'opzione: " choice
        
        case $choice in
            1) show_roles ;;
            2) add_role ;;
            3) show_privileges ;;
            4) add_privilege ;;
            5) show_role_privileges ;;
            6) assign_privilege_to_role ;;
            7) remove_privilege_from_role ;;
            8) show_user_privileges ;;
            9) verify_user_privilege ;;
            10) show_statistics ;;
            11) export_privileges ;;
            0) 
                print_info "Arrivederci!"
                exit 0
                ;;
            *) print_error "Opzione non valida" ;;
        esac
        
        echo ""
        read -p "Premi INVIO per continuare..."
        clear
    done
}

# Esegui il menu principale
main
