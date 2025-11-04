# 📚 Review API Guide - RentHub

## 🎯 Quick Reference

**Base URL**: `http://your-domain.com/api/v1`  
**Authentication**: Bearer Token (for protected endpoints)

---

## 📋 Endpoints Overview

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/reviews` | No | List all reviews |
| GET | `/reviews/{id}` | No | Get single review |
| GET | `/properties/{id}/rating` | No | Get property rating stats |
| GET | `/my-reviews` | Yes | Get user's reviews |
| POST | `/reviews` | Yes | Create new review |
| PUT | `/reviews/{id}` | Yes | Update review |
| DELETE | `/reviews/{id}` | Yes | Delete review |
| POST | `/reviews/{id}/response` | Yes | Add owner response |
| POST | `/reviews/{id}/vote` | Yes | Vote review helpful |

---

## 🔓 Public Endpoints (No Authentication)

### 1. List Reviews

```http
GET /api/v1/reviews
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `property_id` | integer | No | Filter by property ID |
| `min_rating` | integer (1-5) | No | Minimum rating |
| `max_rating` | integer (1-5) | No | Maximum rating |
| `verified_only` | boolean | No | Show only verified guests |
| `has_response` | boolean | No | Filter by owner response |
| `sort_by` | string | No | Sort field: `created_at`, `helpful`, `rating` |
| `sort_order` | string | No | Sort order: `asc`, `desc` |
| `per_page` | integer | No | Results per page (default: 15) |
| `page` | integer | No | Page number |

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/reviews?property_id=5&min_rating=4&sort_by=helpful&per_page=10"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "property_id": 5,
        "user_id": 10,
        "booking_id": 3,
        "rating": 5,
        "comment": "Amazing property! Very clean and comfortable.",
        "cleanliness_rating": 5,
        "communication_rating": 5,
        "check_in_rating": 5,
        "accuracy_rating": 5,
        "location_rating": 4,
        "value_rating": 5,
        "photos": [
          "/storage/reviews/photo1.jpg",
          "/storage/reviews/photo2.jpg"
        ],
        "helpful_count": 12,
        "is_approved": true,
        "owner_response": "Thank you so much!",
        "owner_response_at": "2025-11-02T10:30:00.000000Z",
        "created_at": "2025-11-01T15:20:00.000000Z",
        "updated_at": "2025-11-02T10:30:00.000000Z",
        "user": {
          "id": 10,
          "name": "John Doe",
          "avatar": "/storage/avatars/user10.jpg"
        },
        "booking": {
          "id": 3,
          "status": "completed"
        }
      }
    ],
    "per_page": 10,
    "total": 25
  }
}
```

---

### 2. Get Single Review

```http
GET /api/v1/reviews/{id}
```

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/reviews/1"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "property_id": 5,
    "user_id": 10,
    "rating": 5,
    "comment": "Amazing property!",
    "cleanliness_rating": 5,
    "communication_rating": 5,
    "check_in_rating": 5,
    "accuracy_rating": 5,
    "location_rating": 4,
    "value_rating": 5,
    "photos": ["/storage/reviews/photo1.jpg"],
    "helpful_count": 12,
    "is_approved": true,
    "created_at": "2025-11-01T15:20:00.000000Z",
    "user": { /* user details */ },
    "property": { /* property details */ },
    "booking": { /* booking details */ },
    "responses": [ /* owner responses */ ],
    "helpful_votes": [ /* votes */ ]
  }
}
```

---

### 3. Get Property Rating Statistics

```http
GET /api/v1/properties/{property}/rating
```

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/properties/5/rating"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "average_rating": 4.65,
    "total_reviews": 87,
    "rating_breakdown": {
      "5": 54,
      "4": 23,
      "3": 7,
      "2": 2,
      "1": 1
    },
    "category_averages": {
      "cleanliness": 4.8,
      "communication": 4.9,
      "check_in": 4.7,
      "accuracy": 4.6,
      "location": 4.5,
      "value": 4.7
    }
  }
}
```

---

## 🔐 Protected Endpoints (Authentication Required)

### 4. Get My Reviews

```http
GET /api/v1/my-reviews
```

**Headers:**
```
Authorization: Bearer {your-token}
```

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/my-reviews" \
  -H "Authorization: Bearer your-token-here"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "property_id": 5,
        "rating": 5,
        "comment": "Great stay!",
        "created_at": "2025-11-01T15:20:00.000000Z",
        "property": {
          "id": 5,
          "title": "Beautiful Apartment",
          "main_image": "/storage/properties/main.jpg"
        }
      }
    ],
    "per_page": 15,
    "total": 5
  }
}
```

---

### 5. Create Review

```http
POST /api/v1/reviews
```

**Headers:**
```
Authorization: Bearer {your-token}
Content-Type: multipart/form-data
```

**Form Data:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `property_id` | integer | Yes | Property ID |
| `booking_id` | integer | No | Related booking ID |
| `rating` | integer (1-5) | Yes | Overall rating |
| `comment` | string | No | Review text (max 2000 chars) |
| `cleanliness_rating` | integer (1-5) | No | Cleanliness rating |
| `communication_rating` | integer (1-5) | No | Communication rating |
| `check_in_rating` | integer (1-5) | No | Check-in rating |
| `accuracy_rating` | integer (1-5) | No | Accuracy rating |
| `location_rating` | integer (1-5) | No | Location rating |
| `value_rating` | integer (1-5) | No | Value rating |
| `photos[]` | file | No | Photos (max 5, 5MB each) |

**Example Request (with curl):**
```bash
curl -X POST "http://localhost:8000/api/v1/reviews" \
  -H "Authorization: Bearer your-token" \
  -F "property_id=5" \
  -F "booking_id=3" \
  -F "rating=5" \
  -F "comment=Amazing property!" \
  -F "cleanliness_rating=5" \
  -F "communication_rating=5" \
  -F "check_in_rating=5" \
  -F "accuracy_rating=5" \
  -F "location_rating=4" \
  -F "value_rating=5" \
  -F "photos[]=@/path/to/photo1.jpg" \
  -F "photos[]=@/path/to/photo2.jpg"
```

**Example Request (JavaScript):**
```javascript
const formData = new FormData();
formData.append('property_id', 5);
formData.append('booking_id', 3);
formData.append('rating', 5);
formData.append('comment', 'Amazing property!');
formData.append('cleanliness_rating', 5);
formData.append('communication_rating', 5);
formData.append('check_in_rating', 5);
formData.append('accuracy_rating', 5);
formData.append('location_rating', 4);
formData.append('value_rating', 5);

// Add photos
const photo1 = document.querySelector('#photo1').files[0];
const photo2 = document.querySelector('#photo2').files[0];
formData.append('photos[]', photo1);
formData.append('photos[]', photo2);

const response = await fetch('http://localhost:8000/api/v1/reviews', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

const data = await response.json();
```

**Example Response:**
```json
{
  "success": true,
  "message": "Review submitted successfully",
  "data": {
    "id": 25,
    "property_id": 5,
    "user_id": 10,
    "booking_id": 3,
    "rating": 5,
    "comment": "Amazing property!",
    "cleanliness_rating": 5,
    "communication_rating": 5,
    "check_in_rating": 5,
    "accuracy_rating": 5,
    "location_rating": 4,
    "value_rating": 5,
    "photos": [
      "/storage/reviews/abc123.jpg",
      "/storage/reviews/def456.jpg"
    ],
    "helpful_count": 0,
    "is_approved": true,
    "created_at": "2025-11-02T16:45:00.000000Z",
    "updated_at": "2025-11-02T16:45:00.000000Z",
    "user": { /* user details */ },
    "property": { /* property details */ }
  }
}
```

**Error Responses:**

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "rating": ["The rating field is required."],
    "property_id": ["The selected property id is invalid."]
  }
}
```

**Duplicate Review (422):**
```json
{
  "success": false,
  "message": "You have already reviewed this booking"
}
```

---

### 6. Update Review

```http
PUT /api/v1/reviews/{id}
```

**Headers:**
```
Authorization: Bearer {your-token}
Content-Type: multipart/form-data
```

**Form Data:** (Same as create, all fields optional except rating)

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/v1/reviews/1" \
  -H "Authorization: Bearer your-token" \
  -F "rating=4" \
  -F "comment=Updated review text" \
  -F "cleanliness_rating=4"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Review updated successfully",
  "data": {
    "id": 1,
    "rating": 4,
    "comment": "Updated review text",
    "cleanliness_rating": 4,
    "updated_at": "2025-11-02T17:00:00.000000Z"
  }
}
```

**Error Response - Unauthorized (403):**
```json
{
  "success": false,
  "message": "Unauthorized to edit this review"
}
```

---

### 7. Delete Review

```http
DELETE /api/v1/reviews/{id}
```

**Headers:**
```
Authorization: Bearer {your-token}
```

**Example Request:**
```bash
curl -X DELETE "http://localhost:8000/api/v1/reviews/1" \
  -H "Authorization: Bearer your-token"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Review deleted successfully"
}
```

**Error Response - Unauthorized (403):**
```json
{
  "success": false,
  "message": "Unauthorized to delete this review"
}
```

---

### 8. Add Owner Response

```http
POST /api/v1/reviews/{id}/response
```

**Headers:**
```
Authorization: Bearer {your-token}
Content-Type: application/json
```

**Body:**
```json
{
  "response": "Thank you for your review! We're glad you enjoyed your stay."
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/reviews/1/response" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "response": "Thank you for your review!"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Response added successfully",
  "data": {
    "id": 1,
    "review_id": 5,
    "user_id": 2,
    "response": "Thank you for your review!",
    "created_at": "2025-11-02T17:15:00.000000Z",
    "user": {
      "id": 2,
      "name": "Property Owner",
      "avatar": "/storage/avatars/owner.jpg"
    }
  }
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "response": ["The response field is required."]
  }
}
```

**Unauthorized (403):**
```json
{
  "success": false,
  "message": "Unauthorized to respond to this review"
}
```

---

### 9. Vote Review as Helpful

```http
POST /api/v1/reviews/{id}/vote
```

**Headers:**
```
Authorization: Bearer {your-token}
Content-Type: application/json
```

**Body:**
```json
{
  "is_helpful": true
}
```

**Values:**
- `true` = Mark as helpful
- `false` = Mark as not helpful

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/reviews/1/vote" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{"is_helpful": true}'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Vote recorded successfully",
  "data": {
    "helpful_count": 13
  }
}
```

---

## 🎨 Frontend Integration Examples

### React/Next.js Example

#### Create Review Component
```jsx
import { useState } from 'react';

function CreateReview({ propertyId, bookingId, token }) {
  const [formData, setFormData] = useState({
    rating: 5,
    comment: '',
    cleanliness_rating: 5,
    communication_rating: 5,
    check_in_rating: 5,
    accuracy_rating: 5,
    location_rating: 5,
    value_rating: 5,
    photos: []
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const data = new FormData();
    data.append('property_id', propertyId);
    data.append('booking_id', bookingId);
    data.append('rating', formData.rating);
    data.append('comment', formData.comment);
    data.append('cleanliness_rating', formData.cleanliness_rating);
    data.append('communication_rating', formData.communication_rating);
    data.append('check_in_rating', formData.check_in_rating);
    data.append('accuracy_rating', formData.accuracy_rating);
    data.append('location_rating', formData.location_rating);
    data.append('value_rating', formData.value_rating);
    
    // Add photos
    formData.photos.forEach((photo) => {
      data.append('photos[]', photo);
    });

    try {
      const response = await fetch('http://localhost:8000/api/v1/reviews', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: data
      });

      const result = await response.json();
      
      if (result.success) {
        alert('Review submitted successfully!');
      } else {
        alert('Error: ' + result.message);
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields here */}
    </form>
  );
}
```

#### Display Reviews Component
```jsx
import { useEffect, useState } from 'react';

function ReviewsList({ propertyId }) {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchReviews();
  }, [propertyId]);

  const fetchReviews = async () => {
    try {
      const response = await fetch(
        `http://localhost:8000/api/v1/reviews?property_id=${propertyId}&sort_by=helpful`
      );
      const data = await response.json();
      
      if (data.success) {
        setReviews(data.data.data);
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleVoteHelpful = async (reviewId) => {
    const token = localStorage.getItem('token');
    
    try {
      const response = await fetch(
        `http://localhost:8000/api/v1/reviews/${reviewId}/vote`,
        {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ is_helpful: true })
        }
      );

      const data = await response.json();
      
      if (data.success) {
        // Update helpful count in UI
        fetchReviews();
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="reviews-list">
      {reviews.map(review => (
        <div key={review.id} className="review-card">
          <div className="review-header">
            <img src={review.user.avatar} alt={review.user.name} />
            <h4>{review.user.name}</h4>
            <span className="rating">⭐ {review.rating}/5</span>
          </div>
          
          <p>{review.comment}</p>
          
          {review.photos && review.photos.length > 0 && (
            <div className="review-photos">
              {review.photos.map((photo, index) => (
                <img key={index} src={photo} alt={`Photo ${index + 1}`} />
              ))}
            </div>
          )}
          
          <div className="review-footer">
            <button onClick={() => handleVoteHelpful(review.id)}>
              👍 Helpful ({review.helpful_count})
            </button>
            <span>{new Date(review.created_at).toLocaleDateString()}</span>
          </div>
          
          {review.owner_response && (
            <div className="owner-response">
              <strong>Owner Response:</strong>
              <p>{review.owner_response}</p>
            </div>
          )}
        </div>
      ))}
    </div>
  );
}
```

---

## 📊 Property Rating Display

```jsx
function PropertyRating({ propertyId }) {
  const [rating, setRating] = useState(null);

  useEffect(() => {
    fetchRating();
  }, [propertyId]);

  const fetchRating = async () => {
    try {
      const response = await fetch(
        `http://localhost:8000/api/v1/properties/${propertyId}/rating`
      );
      const data = await response.json();
      
      if (data.success) {
        setRating(data.data);
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  if (!rating) return null;

  return (
    <div className="property-rating">
      <h3>
        ⭐ {rating.average_rating.toFixed(2)} 
        <span>({rating.total_reviews} reviews)</span>
      </h3>
      
      <div className="rating-breakdown">
        {Object.entries(rating.rating_breakdown).reverse().map(([stars, count]) => (
          <div key={stars} className="rating-bar">
            <span>{stars}★</span>
            <div className="bar">
              <div 
                className="fill" 
                style={{ width: `${(count / rating.total_reviews) * 100}%` }}
              />
            </div>
            <span>{count}</span>
          </div>
        ))}
      </div>
      
      <div className="category-ratings">
        <h4>Category Ratings</h4>
        {Object.entries(rating.category_averages).map(([category, score]) => (
          <div key={category} className="category-rating">
            <span>{category}</span>
            <span>⭐ {score.toFixed(1)}</span>
          </div>
        ))}
      </div>
    </div>
  );
}
```

---

## 🔒 Authorization Rules

| Action | Who Can Do It | Notes |
|--------|---------------|-------|
| View Reviews | Everyone | Public endpoint |
| Create Review | Tenants with completed bookings | One review per booking |
| Edit Review | Review author | No time limit |
| Delete Review | Author or Admin | Cascade deletes photos/votes |
| Add Response | Property owner or Admin | Multiple responses allowed |
| Vote Helpful | Any authenticated user | One vote per review |

---

## ✅ Best Practices

1. **Always validate user input** on frontend before sending
2. **Handle errors gracefully** with user-friendly messages
3. **Show loading states** during API calls
4. **Implement image preview** before upload
5. **Compress images** before upload (recommended)
6. **Cache property ratings** to reduce API calls
7. **Implement infinite scroll** for review lists
8. **Show verified guest badge** prominently
9. **Allow filtering and sorting** for better UX
10. **Display owner responses** clearly

---

## 📝 Notes

- **Photo Upload**: Max 5 photos per review, 5MB each
- **Text Limits**: Review comment max 2000 chars, response max 1000 chars
- **Auto-Approval**: Reviews are auto-approved (configurable)
- **Verified Badge**: Shown only for completed bookings
- **Helpful Votes**: Users can change their vote
- **Owner Response**: Latest response shown on review

---

## 🎉 Complete!

You now have a fully functional Review & Rating system with:
- ✅ Multi-category ratings
- ✅ Photo uploads
- ✅ Owner responses
- ✅ Helpful votes
- ✅ Verified guest badges
- ✅ Complete API

**Happy Coding! 🚀**
