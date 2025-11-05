# RentHub API Documentation

## Overview

The RentHub API is a RESTful API built with Laravel 12 that provides endpoints for managing a comprehensive property rental platform. The API supports both short-term and long-term rentals with multi-language and multi-currency capabilities.

## Base URLs

- **Production**: `https://api.renthub.com/api`
- **Staging**: `https://staging-api.renthub.com/api`
- **Development**: `http://localhost:8000/api`

## Authentication

The API uses Laravel Sanctum for authentication. All protected endpoints require a Bearer token.

### Obtaining a Token

**Login**
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "owner"
  }
}
```

### Using the Token

Include the token in the Authorization header:

```http
GET /api/properties
Authorization: Bearer 1|abc123...
```

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/register` | Register new user | No |
| POST | `/auth/login` | Login user | No |
| POST | `/auth/logout` | Logout user | Yes |
| POST | `/auth/refresh` | Refresh token | Yes |
| POST | `/auth/forgot-password` | Request password reset | No |
| POST | `/auth/reset-password` | Reset password | No |
| POST | `/auth/verify-email` | Verify email | Yes |
| GET | `/auth/user` | Get authenticated user | Yes |

### Properties

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/properties` | List properties | No |
| GET | `/properties/{id}` | Get property details | No |
| POST | `/properties` | Create property | Yes (Owner) |
| PUT | `/properties/{id}` | Update property | Yes (Owner) |
| DELETE | `/properties/{id}` | Delete property | Yes (Owner) |
| GET | `/properties/search` | Search properties | No |
| GET | `/properties/{id}/availability` | Check availability | No |
| GET | `/properties/{id}/reviews` | Get property reviews | No |
| GET | `/properties/{id}/similar` | Get similar properties | No |

### Bookings

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/bookings` | List bookings | Yes |
| GET | `/bookings/{id}` | Get booking details | Yes |
| POST | `/bookings` | Create booking | Yes |
| PUT | `/bookings/{id}` | Update booking | Yes |
| DELETE | `/bookings/{id}` | Cancel booking | Yes |
| POST | `/bookings/{id}/confirm` | Confirm booking | Yes (Owner) |
| POST | `/bookings/{id}/reject` | Reject booking | Yes (Owner) |

### Payments

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/payments` | List payments | Yes |
| GET | `/payments/{id}` | Get payment details | Yes |
| POST | `/payments` | Process payment | Yes |
| POST | `/payments/refund` | Refund payment | Yes (Admin) |
| GET | `/payments/methods` | Get payment methods | Yes |

### Messages

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/messages` | List messages | Yes |
| GET | `/messages/{id}` | Get message | Yes |
| POST | `/messages` | Send message | Yes |
| PUT | `/messages/{id}` | Update message | Yes |
| DELETE | `/messages/{id}` | Delete message | Yes |
| GET | `/conversations` | List conversations | Yes |
| GET | `/conversations/{id}` | Get conversation | Yes |

### Reviews

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/reviews` | List reviews | No |
| GET | `/reviews/{id}` | Get review | No |
| POST | `/reviews` | Create review | Yes |
| PUT | `/reviews/{id}` | Update review | Yes (Author) |
| DELETE | `/reviews/{id}` | Delete review | Yes (Author/Admin) |
| POST | `/reviews/{id}/helpful` | Mark review helpful | Yes |

### Users

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/users/{id}` | Get user profile | Yes |
| PUT | `/users/{id}` | Update profile | Yes (Self) |
| GET | `/users/{id}/properties` | Get user properties | Yes |
| GET | `/users/{id}/reviews` | Get user reviews | Yes |
| POST | `/users/{id}/verify` | Submit verification | Yes (Self) |

### Favorites

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/favorites` | List favorites | Yes |
| POST | `/favorites` | Add to favorites | Yes |
| DELETE | `/favorites/{id}` | Remove from favorites | Yes |

### Comparisons

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/comparisons` | List comparisons | Yes |
| POST | `/comparisons` | Add to comparison | Yes |
| DELETE | `/comparisons/{id}` | Remove from comparison | Yes |

### Search

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/search/properties` | Search properties | No |
| GET | `/search/autocomplete` | Autocomplete search | No |
| GET | `/search/filters` | Get available filters | No |

### Translations

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/translations` | Get translations | No |
| GET | `/languages` | List supported languages | No |

### Currency

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/currencies` | List supported currencies | No |
| GET | `/exchange-rates` | Get exchange rates | No |

## Request/Response Format

### Request Headers

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
Accept-Language: en
X-Currency: USD
```

### Response Format

All responses follow this structure:

**Success Response**
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation successful"
}
```

**Error Response**
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error message",
    "details": {}
  }
}
```

### Pagination

List endpoints support pagination:

```http
GET /api/properties?page=1&per_page=20
```

**Response**
```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

## Query Parameters

### Common Parameters

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)
- `sort` - Sort field (e.g., `price`, `-price` for descending)
- `fields` - Comma-separated list of fields to include

### Property Search Parameters

- `location` - Search by location
- `check_in` - Check-in date (YYYY-MM-DD)
- `check_out` - Check-out date (YYYY-MM-DD)
- `guests` - Number of guests
- `price_min` - Minimum price
- `price_max` - Maximum price
- `bedrooms` - Number of bedrooms
- `bathrooms` - Number of bathrooms
- `property_type` - Property type (apartment, house, villa, etc.)
- `amenities` - Comma-separated amenity IDs
- `instant_book` - Boolean (0 or 1)
- `rating_min` - Minimum rating (1-5)

**Example**
```http
GET /api/properties/search?location=Paris&guests=2&price_max=200&instant_book=1
```

## Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created |
| 204 | No Content - Request successful, no content |
| 400 | Bad Request - Invalid request |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation error |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |

## Rate Limiting

The API implements rate limiting:

- **Anonymous users**: 60 requests per minute
- **Authenticated users**: 120 requests per minute
- **Premium users**: 300 requests per minute

Rate limit headers:
```http
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 119
X-RateLimit-Reset: 1677840000
```

## Webhooks

The API supports webhooks for real-time event notifications:

### Supported Events

- `booking.created` - New booking created
- `booking.confirmed` - Booking confirmed
- `booking.cancelled` - Booking cancelled
- `payment.succeeded` - Payment successful
- `payment.failed` - Payment failed
- `review.created` - New review posted
- `message.received` - New message received

### Webhook Configuration

Configure webhooks in the admin panel or via API:

```http
POST /api/webhooks
Content-Type: application/json

{
  "url": "https://your-app.com/webhooks/renthub",
  "events": ["booking.created", "payment.succeeded"],
  "secret": "your-webhook-secret"
}
```

### Webhook Payload

```json
{
  "event": "booking.created",
  "timestamp": "2024-01-01T12:00:00Z",
  "data": {
    "id": 123,
    "property_id": 456,
    "user_id": 789,
    // ... event-specific data
  }
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `VALIDATION_ERROR` | Request validation failed |
| `AUTHENTICATION_ERROR` | Authentication failed |
| `AUTHORIZATION_ERROR` | Insufficient permissions |
| `NOT_FOUND` | Resource not found |
| `CONFLICT` | Resource conflict |
| `RATE_LIMIT_EXCEEDED` | Too many requests |
| `PAYMENT_FAILED` | Payment processing failed |
| `BOOKING_UNAVAILABLE` | Property not available |
| `INTERNAL_ERROR` | Server error |

## Multi-Language Support

The API supports multiple languages. Specify the language in the request header:

```http
Accept-Language: ro
```

Supported languages:
- `en` - English (default)
- `ro` - Romanian
- `es` - Spanish
- `fr` - French
- `de` - German
- `it` - Italian

## Multi-Currency Support

Specify the currency in the request header:

```http
X-Currency: EUR
```

Supported currencies:
- `USD` - US Dollar (default)
- `EUR` - Euro
- `GBP` - British Pound
- `RON` - Romanian Leu

All prices in responses will be converted to the specified currency.

## WebSocket Events

Real-time events via WebSocket (Socket.io):

### Connection

```javascript
const socket = io('wss://api.renthub.com', {
  auth: {
    token: 'your-bearer-token'
  }
});
```

### Events

- `message:received` - New message
- `booking:updated` - Booking status changed
- `notification:new` - New notification
- `price:updated` - Property price changed

## Testing

Use the provided Postman collections in `docs/api/`:

- `SECURITY_POSTMAN_COLLECTION.json` - Security tests
- `SECURITY_POSTMAN_TESTS.json` - Test suite

### Test Credentials

**Development Environment**
```
Email: test@example.com
Password: password
```

## Support

For API support:
- Documentation: https://docs.renthub.com
- Support: support@renthub.com
- GitHub: https://github.com/anemettemadsen33/RentHub

## Changelog

### Version 1.0.0 (Current)
- Initial API release
- Core property management
- Booking system
- Payment processing
- Real-time messaging
- Multi-language support
- Multi-currency support
