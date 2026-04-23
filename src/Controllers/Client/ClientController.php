<?php

namespace App\Controllers\Client;

use App\Controllers\Controller;
use App\Repositories\ClientRepository;
use App\Repositories\DocumentRepository;
use App\Models\Client;
use App\Utils\Logger;
use App\Utils\Uploader;
use App\Utils\Formatter;
use Exception;

/**
 * ClientController - Pivot Central du CRM WinTech V2.5
 * Orchestre la gestion des Tiers, l'analyse de risque et l'historique commercial.
 */
class ClientController extends Controller
{
    private ClientRepository $clientRepo;
    private DocumentRepository $docRepo;

    /**
     * Initialisation avec Middleware de sécurité multiniveaux
     */
    public function __construct()
    {
        parent::__construct();
        
        // Sécurité : Seuls les rôles habilités accèdent au fichier client
        $this->middleware(['COMMERCIAL', 'ADMIN', 'SUPERADMIN', 'COMPTABLE']);
        
        $this->clientRepo = new ClientRepository();
        $this->docRepo = new DocumentRepository();
    }

    /**
     * MODULE 1.B : RÉPERTOIRE GLOBAL DES TIERS
     * Cible : Transformation des lignes SQL en Objets "Cerveau" et calcul des KPIs
     */
    public function index()
    {
        try {
            $agenceId = $_SESSION['agence_id'] ?? null;
            
            // 1. Extraction des données brutes (Data Access Layer)
            $rawData = $this->clientRepo->getAll($agenceId);

            // 2. Transformation de type & Intelligence métier
            $clients = [];
            $stats = [
                'total_fiches' => 0,
                'total_pros'   => 0,
                'risques'      => 0,
                'nouveaux'     => 0
            ];

            $currentMonth = date('m');
            $currentYear  = date('Y');

            foreach ($rawData as $row) {
                // Instanciation du modèle pour bénéficier des méthodes getLabel/getAvatar
                $clientObj = new Client($row);
                $clients[] = $clientObj;

                // Agrégation des indicateurs de performance (KPIs)
                $stats['total_fiches']++;
                
                if ($clientObj->type_client === 'PROFESSIONNEL') {
                    $stats['total_pros']++;
                }

                // Analyse de risque (Inspiré Sage 100)
                if ($clientObj->is_blocked == 1 || ($clientObj->solvabilite_max > 0 && $clientObj->encours_actuel >= $clientObj->solvabilite_max)) {
                    $stats['risques']++;
                }

                // Détection des acquisitions du mois
                $creationTimestamp = strtotime($clientObj->created_at);
                if (date('m', $creationTimestamp) === $currentMonth && date('Y', $creationTimestamp) === $currentYear) {
                    $stats['nouveaux']++;
                }
            }

            // 3. Rendu de l'interface Elite V3
            $this->render('clients/index', [
                'title'      => 'Gestion Clients | WinTech ERP',
                'page_title' => 'Répertoire des Tiers',
                'active'     => 'clients',
                'clients'    => $clients,
                'stats'      => $stats
            ]);

        } catch (Exception $e) {
            Logger::log("CRM_INDEX_FAILURE", $e->getMessage());
            $this->redirect('/dashboard?error=service_indisponible');
        }
    }

    /**
     * MODULE 2 : INTERFACE D'ENRÔLEMENT
     */
    public function create()
    {
        $this->render('clients/create', [
            'page_title' => 'Nouvel Enrôlement',
            'active'     => 'clients',
            'unique_id'  => 'CLI-' . strtoupper(substr(uniqid(), -5))
        ]);
    }

    /**
     * MODULE 1.B & 2 : SAUVEGARDE TRANSACTIONNELLE AVEC LOGO
     */
    public function save()
    {
        $this->middleware(['COMMERCIAL', 'ADMIN', 'SUPERADMIN']);
        $data = $this->request->all();

        // 1. Validation de robustesse (Côté Serveur)
        if (empty($data['nom_prenom']) || empty($data['telephone'])) {
            $this->redirect('/clients/create?error=Veuillez+remplir+les+champs+obligatoires');
        }

        try {
            // 2. Traitement de l'image (Logo Client)
            if (isset($_FILES['logo_client']) && $_FILES['logo_client']['error'] === UPLOAD_ERR_OK) {
                $data['logo_path'] = Uploader::upload($_FILES['logo_client'], 'clients');
            }

            // 3. Normalisation des données avant insertion
            $data['nom_prenom'] = strtoupper($data['nom_prenom']);
            $data['id_agence']  = $_SESSION['agence_id'] ?? 1;

            // 4. Persistance (une seule couche transactionnelle côté PDO si besoin futur)
            $result = $this->clientRepo->save($data);

            if ($result['status'] === 'success') {
                Logger::log("CLIENT_SAVE", "Tiers [{$data['nom_prenom']}] enregistré par {$_SESSION['user_nom']}");
                $this->redirect('/clients?success=Données+synchronisées');
            }

            throw new Exception($result['message'] ?? 'Enregistrement impossible.');

        } catch (Exception $e) {
            Logger::log("CLIENT_SAVE_FAIL", $e->getMessage());
            $this->redirect('/clients/create?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * MODULE 1.C : FICHE HISTORIQUE & ANALYSE DE COMPTE
     * Cible : Suivi de toutes les opérations financières du tiers
     */
    public function history($id)
    {
        try {
            $id = (int)$id;
            $clientData = $this->clientRepo->findById($id);
            
            if (!$clientData) {
                throw new Exception("Le client demandé n'existe pas.");
            }

            $client = new Client($clientData);

            // Mise à jour intelligente de l'encours (Logic Sage)
            $encours = $this->clientRepo->updateEncours($id);
            $client->encours_actuel = $encours;

            $history = $this->docRepo->getAllDocumentsByClient($id);

            $stats = [
                'total_ca'    => 0.0,
                'impayes'     => 0.0,
                'nb_factures' => count($history),
            ];
            foreach ($history as $row) {
                $net = (float) ($row['net_a_payer'] ?? 0);
                $stats['total_ca'] += $net;
                if (($row['statut'] ?? '') !== 'FACTURE_VALIDEE') {
                    $stats['impayes'] += $net;
                }
            }

            $this->render('clients/history', [
                'title'       => 'Dossier Tiers : ' . $client->getLabel(),
                'page_title'  => 'Analyse du Compte',
                'active'      => 'clients',
                'client'      => $client,
                'history'     => $history,
                'stats'       => $stats,
                'formatter'   => new Formatter(),
            ]);

        } catch (Exception $e) {
            $this->redirect('/clients?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * ACTION DE BLOCAGE (SÉCURITÉ FINANCIÈRE)
     */
    public function toggleBlock($id)
    {
        $this->middleware(['ADMIN', 'SUPERADMIN', 'COMPTABLE']);
        
        $client = $this->clientRepo->findById((int)$id);
        $newStatus = ($client['is_blocked'] == 1) ? 0 : 1;
        
        if ($this->clientRepo->toggleStatus((int)$id, $newStatus)) {
            $msg = ($newStatus == 1) ? "Compte bloqué (Risque)" : "Compte débloqué";
            Logger::log("CLIENT_BLOCK_TOGGLE", "$msg pour client ID: $id");
            $this->redirect('/clients?success=' . urlencode($msg));
        }
    }

    /**
     * API AUTOCOMPLETE : Moteur de recherche pour Devis
     */
    public function ajaxSearch()
    {
        $query = $this->request->input('q');
        if (!$query) return $this->json([]);

        $results = $this->clientRepo->search($query);
        
        // Transformation pour le moteur JS
        $formatted = array_map(function($row) {
            $c = new Client($row);
            return [
                'id'    => $c->id,
                'label' => $c->getLabel(),
                'sub'   => $c->telephone . " | " . $c->id_unique_client,
                'type'  => $c->type_client
            ];
        }, $results);

        return $this->json($formatted);
    }
}