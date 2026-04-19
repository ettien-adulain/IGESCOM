<?php
namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Services\AIService;
use App\Utils\Logger;
use Exception;

/**
 * AssistantController V32.0 - API Bridge
 * Gère les interactions entre gia_assistant.js et AIService.php
 * Conforme au Module 8 (IA) et Module 5 (Traçabilité)
 */
class AssistantController extends Controller {

    private AIService $aiService;

    public function __construct() {
        parent::__construct();
        // 1. Sécurité : Vérification de session obligatoire pour l'API
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['reply' => 'Session expirée. Veuillez vous reconnecter.', 'type' => 'error'], 401);
        }
        $this->aiService = new AIService();
    }

    /**
     * ENDPOINT : /api/assistant/ask
     * Traite les messages envoyés par le Chatbot GIA
     */
    public function ask() {
        // 2. Récupération du flux JSON pur (Input asynchrone)
        $data = $this->request->getJson();
        $message = $data['message'] ?? '';

        // 3. Validation de l'entrée
        if (empty(trim($message))) {
            return $this->json(['reply' => 'Je n\'ai pas reçu de message. Comment puis-je vous aider ?', 'type' => 'info']);
        }

        try {
            // 4. Intelligence : Analyse du message via le Service
            // Le service interroge la BDD (Stocks, CA, Clients)
            $response = $this->aiService->processQuery($message);

            // 5. Archivage Électronique (Module 5)
            // On trace la question posée pour améliorer l'IA plus tard
            Logger::log("GIA_QUERY", [
                'user' => $_SESSION['user_nom'],
                'query' => $message,
                'intent_type' => $response['type']
            ]);

            // 6. Réponse formatée
            return $this->json($response);

        } catch (Exception $e) {
            // Log de l'erreur technique pour l'administrateur
            Logger::log("GIA_CRASH", $e->getMessage());

            return $this->json([
                'reply' => "Désolé, mon module de traitement a rencontré une erreur interne. Réessayez dans quelques instants.",
                'type' => 'error',
                'time' => date('H:i')
            ], 500);
        }
    }
}