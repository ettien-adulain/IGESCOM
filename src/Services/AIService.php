<?php

namespace App\Services;

use Core\Container;
use App\Repositories\ArticleRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ClientRepository;
use App\Repositories\HRRepository;
use App\Utils\Formatter;
use App\Utils\Logger;
use Exception;

/**
 * AIService V4.0 - "GIA Assistant Brain"
 * 
 * Ce service agit comme le cerveau analytique de l'ERP IGESCOM.
 * Il traite les requêtes en langage naturel, interroge les couches de données (Repositories)
 * et retourne des analyses stratégiques en temps réel.
 * 
 * @package App\Services
 * @author Expert PHP Natif - WinTech V2.5
 */
class AIService
{
    private ArticleRepository $articleRepo;
    private DocumentRepository $documentRepo;
    private ClientRepository $clientRepo;
    private HRRepository $hrRepo;

    /**
     * Constructeur avec Injection de Dépendances
     * On initialise tous les piliers de données pour que GIA ait une vision 360°.
     */
    public function __construct()
    {
        $this->articleRepo = new ArticleRepository();
        $this->documentRepo = new DocumentRepository();
        $this->clientRepo = new ClientRepository();
        $this->hrRepo = new HRRepository();
    }

    /**
     * MÉTHODE MAÎTRESSE : ANALYZE
     * Résout l'erreur de l'IDE en définissant le point d'entrée du Chatbot.
     * 
     * @param string $prompt La question posée par l'agent
     * @return array Réponse structurée [status, reply, intent, context]
     */
    public function analyze(string $prompt): array
    {
        $query = strtolower(trim($prompt));
        $response = [
            'status' => 'success',
            'reply' => "",
            'intent' => 'unknown',
            'timestamp' => date('H:i:s')
        ];

        try {
            // 1. DÉTECTION DE L'INTENTION (NLP Logique simplifiée)
            if ($this->matchKeywords($query, ['stock', 'rupture', 'épuisé', 'quantité'])) {
                $response['intent'] = 'inventory';
                $response['reply'] = $this->handleStockAnalysis();
            } 
            elseif ($this->matchKeywords($query, ['ca', 'chiffre', 'vente', 'argent', 'gain', 'net'])) {
                $response['intent'] = 'finance';
                $response['reply'] = $this->handleFinanceAnalysis();
            } 
            elseif ($this->matchKeywords($query, ['client', 'tiers', 'acheteur', 'fidélité'])) {
                $response['intent'] = 'crm';
                $response['reply'] = $this->handleClientAnalysis();
            } 
            elseif ($this->matchKeywords($query, ['salaire', 'paie', 'rh', 'employé', 'masse'])) {
                $response['intent'] = 'hr';
                $response['reply'] = $this->handleHRAnalysis();
            }
            elseif ($this->matchKeywords($query, ['aide', 'comment', 'faire', 'procédure'])) {
                $response['intent'] = 'help';
                $response['reply'] = $this->handleHelpLogic();
            }
            else {
                $response['reply'] = "Je suis **GIA**, votre assistant WinTech. Je n'ai pas bien saisi votre demande. Souhaitez-vous une analyse de vos **stocks**, de votre **chiffre d'affaires** ou de vos **tiers** ?";
            }

            // 2. ARCHIVAGE DE LA REQUÊTE (Module 5)
            Logger::log("GIA_QUERY", "Prompt: $query | Intent: " . $response['intent']);

        } catch (Exception $e) {
            Logger::log("GIA_ERROR", $e->getMessage());
            $response['status'] = 'error';
            $response['reply'] = "Désolé, mon module d'analyse a rencontré une erreur technique lors du traitement de vos données.";
        }

        return $response;
    }

    /**
     * ANALYSE DES STOCKS (Module ATIC 6.1)
     */
    private function handleStockAnalysis(): string
    {
        $agenceId = $_SESSION['agence_id'] ?? 1;
        $lowStocks = $this->articleRepo->getLowStock($agenceId);
        $count = count($lowStocks);

        if ($count === 0) {
            return "✅ Excellente nouvelle ! Aucun article n'est actuellement sous le seuil d'alerte dans votre agence.";
        }

        $text = "⚠️ **Alerte Stock :** J'ai détecté **$count articles** en situation critique. <br><br>";
        $text .= "Les 3 articles les plus urgents sont :<br>";
        
        $i = 0;
        foreach ($lowStocks as $item) {
            if ($i++ >= 3) break;
            $text .= "- **{$item['nom']}** (Reste : {$item['quantite']})<br>";
        }

        return $text . "<br>Voulez-vous que je prépare un bon de commande fournisseur ?";
    }

    /**
     * ANALYSE FINANCIÈRE (Module Comptable 6)
     */
    private function handleFinanceAnalysis(): string
    {
        $agenceId = $_SESSION['agence_id'] ?? 1;
        $ca = $this->documentRepo->getTurnover($agenceId, 'month');
        $caDay = $this->documentRepo->getTurnover($agenceId, 'day');

        $text = "📊 **Rapport Flash Finance :**<br>";
        $text .= "- CA du jour : **" . Formatter::fcfa($caDay) . "**<br>";
        $text .= "- CA du mois : **" . Formatter::fcfa($ca) . "**<br><br>";

        if ($ca > 10000000) {
            $text .= "🚀 Performance exceptionnelle ce mois-ci !";
        } else {
            $text .= "📈 Les ventes sont stables, mais prévoyez une relance des proformas en attente.";
        }

        return $text;
    }

    /**
     * ANALYSE CRM (Module 4.1)
     */
    private function handleClientAnalysis(): string
    {
        $clients = $this->clientRepo->getAll();
        $count = count($clients);
        $pros = count(array_filter($clients, fn($c) => $c['type_client'] === 'PROFESSIONNEL'));

        return "🤝 Votre base CRM contient **$count clients**, dont **$pros comptes professionnels**. <br>Votre dernier client enregistré est **" . $clients[0]['nom_prenom'] . "**. <br>Souhaitez-vous consulter l'historique d'un client spécifique ?";
    }

    /**
     * ANALYSE RH (Module 6)
     */
    private function handleHRAnalysis(): string
    {
        $agenceId = $_SESSION['agence_id'] ?? 1;
        $payroll = $this->hrRepo->getMonthlyPayroll($agenceId);
        
        return "👥 **Masse Salariale :** Pour ce mois, le coût total des agents de votre agence s'élève à **" . Formatter::fcfa($payroll) . "**. <br>Cela inclut les salaires de base, les primes et les charges patronales.";
    }

    /**
     * LOGIQUE D'AIDE ET PROCÉDURES
     */
    private function handleHelpLogic(): string
    {
        return "💡 **Assistance Procédures :**<br>
                - Pour un **Devis** : Cliquez sur 'Nouveau Devis' dans le menu ou utilisez le raccourci sur le client.<br>
                - Pour la **DGI** : Validez une proforma en mode 'CLIENT OK', l'interfaçage FNE se déclenchera alors.<br>
                - Pour les **Prix** : Rappelez-vous que la marge de 30% est appliquée automatiquement sur le prix d'achat ATIC.";
    }

    /**
     * Helper de matching de mots-clés
     */
    private function matchKeywords(string $input, array $keywords): bool
    {
        foreach ($keywords as $word) {
            if (str_contains($input, $word)) return true;
        }
        return false;
    }
}