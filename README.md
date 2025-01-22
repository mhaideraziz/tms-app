# Translation Management Service

Translation Management Service is an API-centric application designed to streamline the management of translations. This service includes features for user authentication, translation creation, updates, and JSON export. It focuses on scalability, security, and ease of integration.

## Features

- **Authentication**: Secure login and logout functionality with token-based authentication.
- **User Management**: Create and manage users.
- **Translation Management**:
    - Add translations with support for multiple languages.
    - Search translations by key, tags, or content.
    - Update or delete existing translations.
    - Export translations in JSON format with optional filtering.

## API Documentation

The full API documentation is available in the `api-docs.json` file. You can access the Swagger UI for interactive documentation at the following link:
   ```bash
   for Local https://tms-app.test/api/documentation
   for Server https://tms.reportfunds.com/public/api/documentation
   ```

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/your-repo/translation-management-service.git
   ```

2. Navigate to the project directory:

   ```bash
   cd translation-management-service
   ```

3. Install dependencies:

   ```bash
   composer install
   ```

4. Set up your environment variables:

    - Copy the `.env.example` file to `.env`:

      ```bash
      cp .env.example .env
      ```

    - Update the `.env` file with your database and other configuration details.

5. Run database migrations:

   ```bash
   php artisan migrate
   ```

6. Seed the database with test data (optional):

   ```bash
   php artisan db:seed
   ```
**Note:** Run the `db:seed` command twice to generate 100,000 entries. This approach helps accommodate smaller server specifications.

7. Start the development server:

   ```bash
   php artisan serve
   ```

8. Access the application at [http://localhost:8000](http://localhost:8000).

## Usage

### Authentication

- **Login**: POST `/api/login` with `email` and `password`.
- **Logout**: POST `/api/logout` with the Bearer token.
### Register User

To create a new user, use the following API endpoint:

**Endpoint**: `POST /api/register`

**Request Body**:

```json
{
  "name": "John Doe",
  "email": "johndoe@example.com",
  "password": "securepassword123",
  "password_confirmation": "securepassword123"
}
```

### API Key Usage

All protected endpoints require the `Authorization` header with a Bearer token and the `X-API-KEY` header.
Example:

```http
Authorization: Bearer your-access-token
X-API-KEY: TMS-SECRET-API-KEY
```

### Translations

- **Create Translation**: POST `/api/translations`
- **Update Translation**: PUT `/api/translations/{id}`
- **Delete Translation**: DELETE `/api/translations/{id}`
- **Search Translations**: GET `/api/translations/search`
- **Export Translations**: GET `/api/translations/export`

Refer to the `api-docs.json` file for detailed request and response examples.

## Security

This project uses:
- JWT Bearer token for secure API access.
- API keys for additional authentication.

## Testing

To run the tests:

```bash
php artisan test
```

## Folder Structure

- **Controllers**: API logic for handling user requests (e.g., `AuthController`, `TranslationController`).
- **Services**: Business logic layer (e.g., `AuthService`, `TranslationService`).
- **Repositories**: Data access layer for interacting with the database (e.g., `AuthRepository`, `TranslationRepository`).
- **Middleware**: Custom middleware like `ApiKeyMiddleware` for additional request validation.


---

For more information, contact the project maintainer at [mhaideraziz22@gmail.com](mailto:mhaideraziz22@gmail.com).
