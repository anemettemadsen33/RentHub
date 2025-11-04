# 🧪 Postman Test Collection - Review & Rating System

## 📋 Collection Setup

**Collection Name**: RentHub - Reviews  
**Base URL**: `{{base_url}}` = `http://localhost:8000`  
**Auth Token**: `{{auth_token}}` = Your Bearer Token

---

## 🔧 Environment Variables

Create a Postman environment with these variables:

```json
{
  "base_url": "http://localhost:8000",
  "auth_token": "your-bearer-token-here",
  "property_id": "1",
  "review_id": "1",
  "booking_id": "1"
}
```

---

## 📝 Test Cases

### 1. List All Reviews (Public)

**Request:**
```
GET {{base_url}}/api/v1/reviews
```

**Expected Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ /* reviews array */ ]
  }
}
```

**Tests (Postman):**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Response has data array", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('data');
});
```

---

### 2. Get Reviews for Specific Property

**Request:**
```
GET {{base_url}}/api/v1/reviews?property_id={{property_id}}&sort_by=helpful
```

**Expected Response:** `200 OK`

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("All reviews belong to property", function () {
    var jsonData = pm.response.json();
    jsonData.data.data.forEach(function(review) {
        pm.expect(review.property_id).to.eql(parseInt(pm.environment.get("property_id")));
    });
});
```

---

### 3. Get Property Rating Statistics

**Request:**
```
GET {{base_url}}/api/v1/properties/{{property_id}}/rating
```

**Expected Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "average_rating": 4.65,
    "total_reviews": 87,
    "rating_breakdown": { /* ... */ },
    "category_averages": { /* ... */ }
  }
}
```

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Has rating statistics", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('average_rating');
    pm.expect(jsonData.data).to.have.property('total_reviews');
    pm.expect(jsonData.data).to.have.property('rating_breakdown');
    pm.expect(jsonData.data).to.have.property('category_averages');
});

pm.test("Rating is between 0 and 5", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.average_rating).to.be.within(0, 5);
});
```

---

### 4. Get Single Review

**Request:**
```
GET {{base_url}}/api/v1/reviews/{{review_id}}
```

**Expected Response:** `200 OK`

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Review has required fields", function () {
    var jsonData = pm.response.json();
    var review = jsonData.data;
    pm.expect(review).to.have.property('id');
    pm.expect(review).to.have.property('property_id');
    pm.expect(review).to.have.property('user_id');
    pm.expect(review).to.have.property('rating');
});
```

---

### 5. Create Review (Authenticated)

**Request:**
```
POST {{base_url}}/api/v1/reviews
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: multipart/form-data

Body (form-data):
  property_id: {{property_id}}
  booking_id: {{booking_id}}
  rating: 5
  comment: Amazing property! Very clean and comfortable.
  cleanliness_rating: 5
  communication_rating: 5
  check_in_rating: 5
  accuracy_rating: 5
  location_rating: 5
  value_rating: 5
```

**Expected Response:** `201 Created`
```json
{
  "success": true,
  "message": "Review submitted successfully",
  "data": { /* new review */ }
}
```

**Tests:**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Response has success message", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.message).to.include("submitted successfully");
});

pm.test("Review created with correct data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.rating).to.eql(5);
    pm.expect(jsonData.data.property_id).to.eql(parseInt(pm.environment.get("property_id")));
});

// Save review ID for future tests
pm.environment.set("new_review_id", pm.response.json().data.id);
```

---

### 6. Create Review - Validation Error

**Request:**
```
POST {{base_url}}/api/v1/reviews
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: application/json

Body (JSON):
{
  "property_id": 999999,
  "rating": 10,
  "comment": ""
}
```

**Expected Response:** `422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "property_id": ["The selected property id is invalid."],
    "rating": ["The rating must not be greater than 5."]
  }
}
```

**Tests:**
```javascript
pm.test("Status code is 422", function () {
    pm.response.to.have.status(422);
});

pm.test("Has validation errors", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.false;
    pm.expect(jsonData).to.have.property('errors');
});
```

---

### 7. Create Review with Photos

**Request:**
```
POST {{base_url}}/api/v1/reviews
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: multipart/form-data

Body (form-data):
  property_id: {{property_id}}
  rating: 5
  comment: Great place with beautiful views!
  photos[]: (select file1.jpg)
  photos[]: (select file2.jpg)
```

**Expected Response:** `201 Created`

**Tests:**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Photos uploaded successfully", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.photos).to.be.an('array');
    pm.expect(jsonData.data.photos.length).to.be.greaterThan(0);
});
```

---

### 8. Update Review (Authenticated)

**Request:**
```
PUT {{base_url}}/api/v1/reviews/{{new_review_id}}
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: multipart/form-data

Body (form-data):
  rating: 4
  comment: Updated review text after thinking more about it.
  cleanliness_rating: 4
```

**Expected Response:** `200 OK`
```json
{
  "success": true,
  "message": "Review updated successfully",
  "data": { /* updated review */ }
}
```

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Review updated successfully", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data.rating).to.eql(4);
});
```

---

### 9. Update Review - Unauthorized

**Request:**
```
PUT {{base_url}}/api/v1/reviews/{{review_id}}
Headers:
  Authorization: Bearer {{different_user_token}}
  Content-Type: application/json

Body (JSON):
{
  "rating": 1,
  "comment": "Trying to update someone else's review"
}
```

**Expected Response:** `403 Forbidden`
```json
{
  "success": false,
  "message": "Unauthorized to edit this review"
}
```

**Tests:**
```javascript
pm.test("Status code is 403", function () {
    pm.response.to.have.status(403);
});

pm.test("Unauthorized message", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.false;
    pm.expect(jsonData.message).to.include("Unauthorized");
});
```

---

### 10. Add Owner Response (Owner/Admin Only)

**Request:**
```
POST {{base_url}}/api/v1/reviews/{{review_id}}/response
Headers:
  Authorization: Bearer {{owner_token}}
  Content-Type: application/json

Body (JSON):
{
  "response": "Thank you so much for your wonderful review! We're delighted you enjoyed your stay."
}
```

**Expected Response:** `201 Created`
```json
{
  "success": true,
  "message": "Response added successfully",
  "data": {
    "id": 1,
    "review_id": 1,
    "user_id": 2,
    "response": "Thank you so much for your wonderful review!",
    "created_at": "2025-11-02T17:00:00.000000Z"
  }
}
```

**Tests:**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Response added successfully", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data).to.have.property('response');
});
```

---

### 11. Vote Review as Helpful

**Request:**
```
POST {{base_url}}/api/v1/reviews/{{review_id}}/vote
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: application/json

Body (JSON):
{
  "is_helpful": true
}
```

**Expected Response:** `200 OK`
```json
{
  "success": true,
  "message": "Vote recorded successfully",
  "data": {
    "helpful_count": 13
  }
}
```

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Vote recorded", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data).to.have.property('helpful_count');
});
```

---

### 12. Change Vote to Not Helpful

**Request:**
```
POST {{base_url}}/api/v1/reviews/{{review_id}}/vote
Headers:
  Authorization: Bearer {{auth_token}}
  Content-Type: application/json

Body (JSON):
{
  "is_helpful": false
}
```

**Expected Response:** `200 OK`

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Vote changed successfully", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});
```

---

### 13. Get My Reviews (Authenticated)

**Request:**
```
GET {{base_url}}/api/v1/my-reviews
Headers:
  Authorization: Bearer {{auth_token}}
```

**Expected Response:** `200 OK`

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Returns user's reviews only", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.data).to.be.an('array');
});
```

---

### 14. Delete Review (Authenticated)

**Request:**
```
DELETE {{base_url}}/api/v1/reviews/{{new_review_id}}
Headers:
  Authorization: Bearer {{auth_token}}
```

**Expected Response:** `200 OK`
```json
{
  "success": true,
  "message": "Review deleted successfully"
}
```

**Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Review deleted", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.message).to.include("deleted successfully");
});
```

---

### 15. Delete Review - Unauthorized

**Request:**
```
DELETE {{base_url}}/api/v1/reviews/{{review_id}}
Headers:
  Authorization: Bearer {{different_user_token}}
```

**Expected Response:** `403 Forbidden`

**Tests:**
```javascript
pm.test("Status code is 403", function () {
    pm.response.to.have.status(403);
});
```

---

## 📊 Complete Test Flow

### Scenario 1: Guest Leaves Review
1. ✅ Guest completes booking
2. ✅ Guest creates review with photos (Test #7)
3. ✅ Guest votes other review as helpful (Test #11)
4. ✅ Guest views property rating (Test #3)
5. ✅ Guest edits own review (Test #8)

### Scenario 2: Owner Responds
1. ✅ Owner views property reviews (Test #2)
2. ✅ Owner adds response to review (Test #10)
3. ✅ Owner checks property rating stats (Test #3)

### Scenario 3: Admin Moderates
1. ✅ Admin views all reviews (Test #1)
2. ✅ Admin views single review details (Test #4)
3. ✅ Admin deletes inappropriate review (Test #14)

---

## 🔄 Test Execution Order

Run tests in this order for best results:

1. **Setup Tests**
   - Test #1: List All Reviews
   - Test #3: Get Property Rating
   - Test #4: Get Single Review

2. **Create Tests**
   - Test #5: Create Review
   - Test #6: Create Review - Validation Error
   - Test #7: Create Review with Photos

3. **Interaction Tests**
   - Test #11: Vote Helpful
   - Test #12: Change Vote
   - Test #10: Add Owner Response

4. **Update Tests**
   - Test #8: Update Review
   - Test #9: Update - Unauthorized

5. **View Tests**
   - Test #2: Get Property Reviews
   - Test #13: Get My Reviews

6. **Delete Tests**
   - Test #14: Delete Review
   - Test #15: Delete - Unauthorized

---

## 🎯 Success Criteria

All tests should pass with:
- ✅ Correct status codes
- ✅ Valid JSON responses
- ✅ Proper error handling
- ✅ Authorization checks working
- ✅ Data validation working
- ✅ Photos uploading correctly
- ✅ Relationships loaded properly

---

## 📝 Notes

- **Token Required**: Most endpoints need authentication
- **File Uploads**: Use multipart/form-data for photos
- **Rate Limiting**: API may have rate limits
- **Environment**: Adjust base_url for production
- **Test Data**: Create test properties and bookings first

---

## 🎉 Complete!

You now have a complete Postman test suite for the Review & Rating System!

**Import this into Postman and start testing! 🚀**
