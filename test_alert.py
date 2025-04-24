import requests

data = {
    'name': 'PHP Fatal Error',
    'condition': 'Message LIKE "%Call to a member function exec() on null%"',
    'priority': 'critical',
    'description': 'Detects PHP Fatal errors in settings.php',
    'notification_method': 'email',
    'notification_target': 'admin@example.com'
}

print("Making request to save alert...")
response = requests.post('http://localhost:8080/json/save_alert.php', json=data)
print(f"Status Code: {response.status_code}")
print(f"Response Body: {response.text}")
print(f"Response Headers: {dict(response.headers)}") 