# Order Management API

This document describes the API endpoints for the order management system with payment functionality.

## Base URL
`/api/orders`

## Endpoints

### 1. Get All Orders
- **Method**: GET
- **URL**: `/api/orders`
- **Description**: Retrieve all orders with their associated user and order items
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "table_number": "Table 1",
      "status": "paid",
      "total_price": "25.50",
      "created_at": "2023-10-03T10:30:00.000000Z",
      "updated_at": "2023-10-03T10:45:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "orderItems": [
        {
          "id": 1,
          "order_id": 1,
          "menu_id": 1,
          "quantity": 2,
          "price": "12.75",
          "menu": {
            "id": 1,
            "name": "Coffee",
            "price": "12.75"
          }
        }
      ]
    }
  ]
}
```

### 2. Create New Order
- **Method**: POST
- **URL**: `/api/orders`
- **Description**: Create a new order
- **Authentication**: Required
- **Request Body**:
```json
{
  "table_number": "Table 5",
  "user_id": 1,
  "items": [
    {
      "menu_id": 1,
      "quantity": 2
    },
    {
      "menu_id": 3,
      "quantity": 1
    }
  ]
}
```
- **Validation**:
  - `table_number` is required (string, max 255 chars)
  - `user_id` is optional (must exist in users table)
  - `items` is required (array, minimum 1 item)
  - Each item must have `menu_id` (must exist in menus table) and `quantity` (integer, minimum 1)

- **Response**:
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 2,
    "user_id": 1,
    "table_number": "Table 5",
    "status": "pending",
    "total_price": "38.25",
    "created_at": "2023-10-03T11:00:00.000000Z",
    "updated_at": "2023-10-03T11:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "orderItems": [
      {
        "id": 2,
        "order_id": 2,
        "menu_id": 1,
        "quantity": 2,
        "price": "12.75",
        "menu": {
          "id": 1,
          "name": "Coffee",
          "price": "12.75"
        }
      },
      {
        "id": 3,
        "order_id": 2,
        "menu_id": 3,
        "quantity": 1,
        "price": "12.75",
        "menu": {
          "id": 3,
          "name": "Tea",
          "price": "12.75"
        }
      }
    ]
  }
}
```

### 3. Get Specific Order
- **Method**: GET
- **URL**: `/api/orders/{id}`
- **Description**: Retrieve a specific order by ID
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "table_number": "Table 1",
    "status": "paid",
    "total_price": "25.50",
    "created_at": "2023-10-03T10:30:00.000000Z",
    "updated_at": "2023-10-03T10:45:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "orderItems": [
      {
        "id": 1,
        "order_id": 1,
        "menu_id": 1,
        "quantity": 2,
        "price": "12.75",
        "menu": {
          "id": 1,
          "name": "Coffee",
          "price": "12.75"
        }
      }
    ]
  }
}
```

### 4. Update Order Status (Generic)
- **Method**: PUT
- **URL**: `/api/orders/{id}`
- **Description**: Update order status
- **Authentication**: Required
- **Request Body**:
```json
{
  "status": "completed"
}
```
- **Validation**:
  - `status` is required (must be one of: pending, paid, completed)
- **Response**:
```json
{
  "success": true,
  "message": "Order updated successfully",
  "data": {
    "id": 1,
    // ... order data
  }
}
```

### 5. Mark Order as Paid
- **Method**: PATCH
- **URL**: `/api/orders/{id}/mark-paid`
- **Description**: Mark an order as paid (changes status to 'paid')
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Order marked as paid successfully",
  "data": {
    "id": 1,
    "status": "paid",
    // ... rest of order data
  }
}
```

### 6. Mark Order as Completed
- **Method**: PATCH
- **URL**: `/api/orders/{id}/mark-completed`
- **Description**: Mark an order as completed (only if status is 'paid')
- **Authentication**: Required
- **Response**:
```json
{
  "success": true,
  "message": "Order marked as completed successfully",
  "data": {
    "id": 1,
    "status": "completed",
    // ... rest of order data
  }
}
```

## Order Status Flow

1. **pending** - Initial status when order is created
2. **paid** - Set when payment is processed (can be marked with `/mark-paid` endpoint)
3. **completed** - Final status when order is fulfilled (can be marked with `/mark-completed` endpoint)

## Frontend Integration Example

For React applications, you can use the following service to interact with the API:

```javascript
// OrderService.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const orderService = {
  // Get all orders
  getAllOrders: async () => {
    try {
      const response = await axios.get(`${API_BASE_URL}/orders`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Create new order
  createOrder: async (orderData) => {
    try {
      const response = await axios.post(`${API_BASE_URL}/orders`, orderData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Mark order as paid
  markOrderAsPaid: async (orderId) => {
    try {
      const response = await axios.patch(`${API_BASE_URL}/orders/${orderId}/mark-paid`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Mark order as completed
  markOrderAsCompleted: async (orderId) => {
    try {
      const response = await axios.patch(`${API_BASE_URL}/orders/${orderId}/mark-completed`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  }
};

export default orderService;
```

## Error Handling

All API endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information" // Only in 500 errors
}
```

Common status codes:
- 200: Success
- 201: Created
- 400: Bad Request (validation errors)
- 404: Not Found
- 500: Server Error