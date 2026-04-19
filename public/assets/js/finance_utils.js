/**
 * WINTECH ERP - MOTEUR FINANCIER ÉVOLUÉ
 * Calcul automatique de l'IBAN selon la norme ISO 7064 (Modulo 97)
 */

document.addEventListener('DOMContentLoaded', function() {
    const ids = ['b_code', 'g_code', 'c_num', 'r_key'];
    const autoCheck = document.getElementById('auto_iban');
    const resultInput = document.getElementById('iban_result');

    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                if (autoCheck && autoCheck.checked) computeIban();
            });
        }
    });

    function computeIban() {
        const b = document.getElementById('b_code').value.padStart(5, '0');
        const g = document.getElementById('g_code').value.padStart(5, '0');
        const c = document.getElementById('c_num').value.padStart(12, '0');
        const k = document.getElementById('r_key').value.padStart(2, '0');
        const country = "CI";

        // Pour la Côte d'Ivoire, l'IBAN est : CI + 2 chiffres de clé + 24 chiffres (BBAN)
        // La clé se calcule sur : BBAN + 1218 (CI) + 00
        if (b != '00000' && c != '000000000000') {
            const bban = b + g + c + k;
            const checkDigits = calculateMod97(bban, country);
            resultInput.value = `${country}${checkDigits} ${b} ${g} ${c} ${k}`.toUpperCase();
        }
    }

    function calculateMod97(bban, countryCode) {
        // Transformation CI -> 12 18
        const numericCountry = "121800";
        const totalString = bban + numericCountry;
        
        // Calcul Modulo 97 sur de très grands nombres (BigInt)
        let remainder = BigInt(totalString) % 97n;
        let check = 98n - remainder;
        return check.toString().padStart(2, '0');
    }
});