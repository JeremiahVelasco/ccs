# Mobile API Guide for Task Management

## Overview

This guide explains how to use the enhanced Task API for mobile applications, including file upload functionality.

## Authentication

All endpoints require authentication using Laravel Sanctum tokens. Include the token in the Authorization header:

```
Authorization: Bearer <your-token>
```

## Base URL

```
https://your-domain.com/api
```

## Task Management Endpoints

### 1. Create Task

**POST** `/tasks`

Creates a new task with optional file upload support.

#### Request Headers

```
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

#### Request Body (Form Data)

```
title: string (required) - Task title
description: string (optional) - Task description
deadline: string (optional) - Date in Y-m-d format
assigned_to: array (optional) - Array of user IDs
file: file (optional) - File upload (max 10MB)
type: string (optional) - "development" or "documentation" (default: "development")
status: string (optional) - "To-do", "In Progress", "For Review", "Approved" (default: "To-do")
```

#### Supported File Types

-   Documents: pdf, doc, docx, txt, csv, xlsx, pptx
-   Images: jpg, jpeg, png, gif
-   Archives: zip, rar

#### Success Response (201)

```json
{
    "success": true,
    "message": "Task created successfully",
    "data": {
        "task": {
            "id": 1,
            "project_id": 1,
            "title": "Mobile App Development",
            "description": "Develop mobile application",
            "deadline": "2024-12-31",
            "type": "development",
            "status": "To-do",
            "assigned_to": [1, 2],
            "file_path": "task-files/1234567890_document.pdf",
            "sort": null,
            "is_faculty_approved": false,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        "file_url": "https://your-domain.com/storage/task-files/1234567890_document.pdf",
        "project": {
            "id": 1,
            "title": "My Project"
        }
    }
}
```

#### Error Response (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "title": ["The title field is required."],
        "file": ["The file may not be greater than 10240 kilobytes."]
    }
}
```

### 2. Get Task

**GET** `/tasks/{taskId}`

Retrieves a specific task with file URL.

#### Success Response (200)

```json
{
    "success": true,
    "data": {
        "task": {
            "id": 1,
            "project_id": 1,
            "title": "Mobile App Development",
            "description": "Develop mobile application",
            "deadline": "2024-12-31",
            "type": "development",
            "status": "To-do",
            "assigned_to": [1, 2],
            "file_path": "task-files/1234567890_document.pdf",
            "sort": null,
            "is_faculty_approved": false,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z",
            "project": {
                "id": 1,
                "title": "My Project"
            }
        },
        "file_url": "https://your-domain.com/storage/task-files/1234567890_document.pdf"
    }
}
```

### 3. Update Task

**PUT** `/tasks/{taskId}`

Updates a task with optional file upload.

#### Request Body (Form Data)

```
title: string (optional) - Task title
description: string (optional) - Task description
deadline: string (optional) - Date in Y-m-d format
assigned_to: array (optional) - Array of user IDs
file: file (optional) - File upload (replaces existing file)
type: string (optional) - "development" or "documentation"
status: string (optional) - "To-do", "In Progress", "For Review", "Approved"
```

#### Success Response (200)

```json
{
    "success": true,
    "message": "Task updated successfully",
    "data": {
        "task": {
            /* updated task data */
        },
        "file_url": "https://your-domain.com/storage/task-files/new_file.pdf",
        "project": {
            "id": 1,
            "title": "My Project"
        }
    }
}
```

### 4. Delete Task

**DELETE** `/tasks/{taskId}`

Deletes a task and its associated file.

#### Success Response (200)

```json
{
    "success": true,
    "message": "Task deleted successfully"
}
```

#### Error Response (400)

```json
{
    "success": false,
    "message": "Cannot delete documentation task"
}
```

### 5. Get Documentation Tasks

**GET** `/tasks/documentation`

Retrieves all documentation tasks for the user's project.

#### Success Response (200)

```json
[
    {
        "id": 1,
        "project_id": 1,
        "title": "Title Page",
        "description": null,
        "deadline": null,
        "type": "documentation",
        "status": "To-do",
        "assigned_to": null,
        "file_path": null,
        "sort": null,
        "is_faculty_approved": false,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
]
```

### 6. Get Development Tasks

**GET** `/tasks/development`

Retrieves all development tasks for the user's project.

#### Success Response (200)

```json
[
    {
        "id": 2,
        "project_id": 1,
        "title": "Backend API",
        "description": "Develop REST API",
        "deadline": "2024-12-31",
        "type": "development",
        "status": "In Progress",
        "assigned_to": [1, 2],
        "file_path": "task-files/api_spec.pdf",
        "sort": 1,
        "is_faculty_approved": false,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
]
```

## Mobile Implementation Examples

### React Native Example

```javascript
// Create task with file upload
const createTask = async (taskData, fileUri) => {
    const formData = new FormData();
    formData.append("title", taskData.title);
    formData.append("description", taskData.description);
    formData.append("deadline", taskData.deadline);
    formData.append("type", taskData.type);
    formData.append("status", taskData.status);

    if (fileUri) {
        formData.append("file", {
            uri: fileUri,
            type: "application/pdf",
            name: "task_file.pdf",
        });
    }

    try {
        const response = await fetch("https://your-domain.com/api/tasks", {
            method: "POST",
            headers: {
                Authorization: `Bearer ${authToken}`,
                "Content-Type": "multipart/form-data",
            },
            body: formData,
        });

        const result = await response.json();
        return result;
    } catch (error) {
        console.error("Error creating task:", error);
        throw error;
    }
};
```

### Flutter Example

```dart
// Create task with file upload
Future<Map<String, dynamic>> createTask(Map<String, dynamic> taskData, File? file) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('https://your-domain.com/api/tasks'),
  );

  request.headers['Authorization'] = 'Bearer $authToken';
  request.fields['title'] = taskData['title'];
  request.fields['description'] = taskData['description'];
  request.fields['deadline'] = taskData['deadline'];
  request.fields['type'] = taskData['type'];
  request.fields['status'] = taskData['status'];

  if (file != null) {
    request.files.add(
      await http.MultipartFile.fromPath('file', file.path)
    );
  }

  try {
    var response = await request.send();
    var responseData = await response.stream.bytesToString();
    return json.decode(responseData);
  } catch (error) {
    print('Error creating task: $error');
    throw error;
  }
}
```

## Error Handling

### Common Error Responses

#### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

#### 403 Forbidden

```json
{
    "success": false,
    "message": "Unauthorized access to this task"
}
```

#### 404 Not Found

```json
{
    "success": false,
    "message": "Task not found"
}
```

#### 422 Validation Error

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "title": ["The title field is required."],
        "file": [
            "The file must be a file of type: pdf, doc, docx, jpg, jpeg, png, gif, zip, rar, txt, csv, xlsx, pptx."
        ]
    }
}
```

#### 500 Server Error

```json
{
    "success": false,
    "message": "Failed to create task",
    "error": "Internal server error message"
}
```

## Security Considerations

1. **Authentication**: All endpoints require valid authentication tokens
2. **Authorization**: Users can only access tasks from their own project/group
3. **File Validation**: Files are validated for type and size
4. **File Storage**: Files are stored securely with unique names
5. **Input Validation**: All inputs are validated before processing

## Best Practices

1. **File Uploads**: Always validate file types and sizes on the client side
2. **Error Handling**: Implement proper error handling for all API calls
3. **Token Management**: Store and manage authentication tokens securely
4. **Progress Indicators**: Show upload progress for file uploads
5. **Offline Support**: Cache data locally when possible
6. **Retry Logic**: Implement retry mechanisms for failed requests

## Testing

Use tools like Postman, Insomnia, or curl to test the API endpoints:

```bash
# Create task with file upload
curl -X POST "https://your-domain.com/api/tasks" \
  -H "Authorization: Bearer your-token-here" \
  -F "title=Test Task" \
  -F "description=Test Description" \
  -F "type=development" \
  -F "file=@/path/to/your/file.pdf"
```

This enhanced API provides a robust foundation for mobile app integration with comprehensive file upload support, proper error handling, and security measures.
