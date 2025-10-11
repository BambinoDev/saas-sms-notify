# API CPN SMS Reminder

## Authentication
Toutes les routes (sauf `/health`) nécessitent un Bearer Token :

```
Authorization: Bearer cpn_sms_2025_secure_token_change_in_production
```

## Base URL
```
http://localhost:8000/api
```

## Endpoints

### 1. Health Check
**GET** `/health`

Vérifier l'état de l'API (sans authentification).

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2025-09-29T14:46:29.000000Z"
}
```

### 2. Récupérer SMS en attente
**GET** `/sms/pending`

Récupérer les SMS prêts à être envoyés.

**Query Parameters:**
- `limit` (optional): Nombre maximum de SMS à retourner (défaut: 50)

**Response:**
```json
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": 1,
      "recipient_phone": "2250707070707",
      "message": "Bonjour Mme Marie Test, venez après-demain au Centre de Santé pour votre 3ème Consultation Prénatale. Ne manquez pas ce Rendez-vous !",
      "type": "j-2",
      "scheduled_at": "2025-10-01T08:00:00.000000Z",
      "woman": {
        "id": 1,
        "name": "Marie Test",
        "case_id": "TEST123"
      }
    }
  ]
}
```

### 3. Marquer SMS comme envoyé
**POST** `/sms/{id}/sent`

Marquer un SMS comme envoyé avec succès.

**Request Body:**
```json
{
  "gateway_response": "Success - Message sent",
  "sent_at": "2025-09-29T14:46:29.000000Z"
}
```

**Response:**
```json
{
  "success": true,
  "message": "SMS marqué comme envoyé",
  "sms": {
    "id": 1,
    "status": "sent",
    "sent_at": "2025-09-29T14:46:29.000000Z"
  }
}
```

### 4. Marquer SMS comme livré
**POST** `/sms/{id}/delivered`

Marquer un SMS comme livré au destinataire.

**Response:**
```json
{
  "success": true,
  "message": "SMS marqué comme livré",
  "sms": {
    "id": 1,
    "status": "delivered",
    "delivered_at": "2025-09-29T14:46:29.000000Z"
  }
}
```

### 5. Marquer SMS comme échoué
**POST** `/sms/{id}/failed`

Marquer un SMS comme échoué.

**Request Body:**
```json
{
  "error_message": "Network timeout",
  "error_details": "Connection failed after 30 seconds"
}
```

**Response:**
```json
{
  "success": true,
  "message": "SMS marqué comme échoué",
  "sms": {
    "id": 1,
    "status": "failed",
    "error_message": "Network timeout",
    "can_retry": true
  }
}
```

### 6. Réessayer un SMS échoué
**POST** `/sms/{id}/retry`

Remettre un SMS échoué en queue pour nouvelle tentative.

**Response:**
```json
{
  "success": true,
  "message": "SMS remis en queue",
  "sms": {
    "id": 1,
    "status": "pending",
    "retry_count": 1
  }
}
```

### 7. Statistiques
**GET** `/sms/stats`

Obtenir les statistiques de l'API SMS.

**Query Parameters:**
- `days` (optional): Période en jours (défaut: 30)

**Response:**
```json
{
  "success": true,
  "period": "30 jours",
  "stats": {
    "total_sent": 150,
    "successful": 142,
    "failed": 8,
    "delivered": 135,
    "success_rate": 94.67,
    "pending": 5,
    "ready_to_send": 3
  }
}
```

## Codes d'erreur

### 400 Bad Request
- SMS déjà traité
- Statut invalide pour l'opération
- SMS ne peut pas être réessayé

### 401 Unauthorized
- Token manquant ou invalide

### 404 Not Found
- SMS non trouvé

### 422 Unprocessable Entity
- Erreurs de validation des données

### 500 Internal Server Error
- Erreur serveur interne

## Exemples d'utilisation Flutter

### Récupérer SMS à envoyer
```dart
final response = await http.get(
  Uri.parse('http://localhost:8000/api/sms/pending'),
  headers: {
    'Authorization': 'Bearer cpn_sms_2025_secure_token_change_in_production',
    'Content-Type': 'application/json',
  },
);
```

### Marquer SMS comme envoyé
```dart
final response = await http.post(
  Uri.parse('http://localhost:8000/api/sms/1/sent'),
  headers: {
    'Authorization': 'Bearer cpn_sms_2025_secure_token_change_in_production',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'gateway_response': 'Success - Message sent',
  }),
);
```

### Marquer SMS comme échoué
```dart
final response = await http.post(
  Uri.parse('http://localhost:8000/api/sms/1/failed'),
  headers: {
    'Authorization': 'Bearer cpn_sms_2025_secure_token_change_in_production',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'error_message': 'Network timeout',
    'error_details': 'Connection failed after 30 seconds',
  }),
);
```

## Workflow recommandé

1. **Récupérer SMS** : App Flutter appelle `/sms/pending`
2. **Envoyer SMS** : App Flutter envoie le SMS via le réseau mobile
3. **Confirmer envoi** : App Flutter appelle `/sms/{id}/sent`
4. **Confirmer livraison** : App Flutter appelle `/sms/{id}/delivered` (si supporté)
5. **Gérer les échecs** : En cas d'échec, appeler `/sms/{id}/failed`
6. **Retry** : Si possible, appeler `/sms/{id}/retry` pour réessayer

## Sécurité

- Token à changer en production
- Utiliser HTTPS en production
- Limiter les tentatives de retry
- Logger toutes les interactions
- Surveiller les statistiques de succès
