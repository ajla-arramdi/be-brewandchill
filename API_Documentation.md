# Brew & Chill API Documentation

## Authentication

The API uses Laravel Sanctum for authentication. All authenticated endpoints require a valid Bearer token in the Authorization header.

```
Authorization: Bearer {access_token}
```

## Available Roles
- `admin`: Full system access
- `cashier`: Order management and payment processing
- `user`: Standard user access (create/view own orders, view menu)

## API Endpoints

### Authentication

#### Register User
- **POST** `/api/register`
- **Description**: Register a new user account
- **Permissions**: Public
- **Request Body**:
```json
{
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
}
```
- **Response**:
```json
{
    "user": {...},
    "access_token": "string",
    "token_type": "Bearer"
}
```

#### Login
- **POST** `/api/login`
- **Description**: Authenticate user and get access token
- **Permissions**: Public
- **Request Body**:
```json
{
    "email": "string",
    "password": "string"
}
```
- **Response**:
```json
{
    "user": {...},
    "access_token": "string",
    "token_type": "Bearer"
}
```

#### Get User Profile
- **GET** `/api/user`
- **Description**: Get authenticated user's profile
- **Permissions**: Authenticated users
- **Response**:
```json
{
    "user": {...},
    "roles": ["array of role names"]
}
```

#### Logout
- **POST** `/api/logout`
- **Description**: Logout and revoke current token
- **Permissions**: Authenticated users
- **Response**:
```json
{
    "message": "Logged out successfully"
}
```

#### Change Password
- **PUT** `/api/change-password`
- **Description**: Change authenticated user's password
- **Permissions**: Authenticated users
- **Request Body**:
```json
{
    "current_password": "string",
    "new_password": "string",
    "new_password_confirmation": "string"
}
```
- **Response**:
```json
{
    "message": "Password changed successfully"
}
```

### Categories (Admin Only)

#### List Categories
- **GET** `/api/categories`
- **Description**: Get all categories
- **Permissions**: Admin
- **Response**: Array of categories

#### Create Category
- **POST** `/api/categories`
- **Description**: Create a new category
- **Permissions**: Admin
- **Request Body**:
```json
{
    "name": "string"
}
```
- **Response**: Created category

#### Show Category
- **GET** `/api/categories/{id}`
- **Description**: Get a specific category
- **Permissions**: Admin
- **Response**: Category object

#### Update Category
- **PUT** `/api/categories/{id}`
- **Description**: Update a category
- **Permissions**: Admin
- **Request Body**:
```json
{
    "name": "string"
}
```
- **Response**: Updated category

#### Delete Category
- **DELETE** `/api/categories/{id}`
- **Description**: Delete a category
- **Permissions**: Admin
- **Response**:
```json
{
    "message": "Category deleted"
}
```

### Menus (Admin Only)

#### List Menus
- **GET** `/api/menus`
- **Description**: Get all menu items
- **Permissions**: Admin
- **Response**: Array of menu items with categories

#### Create Menu
- **POST** `/api/menus`
- **Description**: Create a new menu item
- **Permissions**: Admin
- **Request Body**:
```json
{
    "name": "string",
    "description": "string (optional)",
    "price": "number",
    "category_id": "number"
}
```
- **Response**: Created menu item

#### Show Menu
- **GET** `/api/menus/{id}`
- **Description**: Get a specific menu item
- **Permissions**: Admin
- **Response**: Menu item with category

#### Update Menu
- **PUT** `/api/menus/{id}`
- **Description**: Update a menu item
- **Permissions**: Admin
- **Request Body**:
```json
{
    "name": "string",
    "description": "string (optional)",
    "price": "number",
    "category_id": "number"
}
```
- **Response**: Updated menu item

#### Delete Menu
- **DELETE** `/api/menus/{id}`
- **Description**: Delete a menu item
- **Permissions**: Admin
- **Response**:
```json
{
    "message": "Menu deleted"
}
```

### Orders

#### List Orders
- **GET** `/api/orders`
- **Description**: Get orders
- **Permissions**: 
  - Admin/Cashier: All orders
  - User: Own orders only
- **Response**: Array of orders

#### Create Order
- **POST** `/api/orders`
- **Description**: Create a new order
- **Permissions**: Authenticated users
- **Request Body**:
```json
{
    "table_number": "string",
    "user_id": "number (optional, defaults to authenticated user)",
    "items": [
        {
            "menu_id": "number",
            "quantity": "number"
        }
    ]
}
```
- **Response**: Created order details

#### Show Order
- **GET** `/api/orders/{id}`
- **Description**: Get a specific order
- **Permissions**: 
  - Admin/Cashier: Any order
  - User: Own orders only
- **Response**: Order details

### Order Management (Role-Specific)

#### Mark Order as Paid
- **PATCH** `/api/cashier/orders/{id}/mark-paid` (Cashier) or `/api/admin/orders/{id}/mark-paid` (Admin)
- **Description**: Mark an order as paid
- **Permissions**: Cashier or Admin
- **Response**: Updated order

#### Mark Order as Completed
- **PATCH** `/api/cashier/orders/{id}/mark-completed` (Cashier) or `/api/admin/orders/{id}/mark-completed` (Admin)
- **Description**: Mark an order as completed (must be paid first)
- **Permissions**: Cashier or Admin
- **Response**: Updated order

## Error Responses

All error responses follow this format:
```json
{
    "message": "Error message"
}
```

Or for validation errors:
```json
{
    "errors": {
        "field_name": ["validation messages"]
    }
}
```

## HTTP Status Codes

- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden (Insufficient permissions)
- `404`: Not Found
- `422`: Validation Error
- `500`: Server Error