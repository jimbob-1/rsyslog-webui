#!/usr/bin/env python3
import socket
import time
import random
import datetime
import sys
import os

# Syslog facilities
FACILITIES = {
    'kern': 0,
    'user': 1,
    'mail': 2,
    'daemon': 3,
    'auth': 4,
    'syslog': 5,
    'lpr': 6,
    'news': 7,
    'uucp': 8,
    'cron': 9,
    'authpriv': 10,
    'ftp': 11,
    'local0': 16,
    'local1': 17,
    'local2': 18,
    'local3': 19,
    'local4': 20,
    'local5': 21,
    'local6': 22,
    'local7': 23
}

# Syslog severities
SEVERITIES = {
    'emerg': 0,
    'alert': 1,
    'crit': 2,
    'err': 3,
    'warning': 4,
    'notice': 5,
    'info': 6,
    'debug': 7
}

# Sample log messages
MESSAGES = {
    'kern': [
        'Kernel: CPU temperature above threshold',
        'Kernel: Out of memory',
        'Kernel: Disk I/O error',
        'Kernel: Network interface down'
    ],
    'auth': [
        'Failed login attempt for user root',
        'Successful login for user admin',
        'Password change requested',
        'SSH connection established'
    ],
    'daemon': [
        'Service started: nginx',
        'Service stopped: apache2',
        'Service failed to start: mysql',
        'Service restarted: rsyslog'
    ],
    'mail': [
        'Mail server queue full',
        'Spam detected',
        'Mail delivery failed',
        'New mail received'
    ],
    'cron': [
        'Cron job completed successfully',
        'Cron job failed',
        'Cron job started',
        'Cron job scheduled'
    ]
}

def generate_syslog_message():
    # Select random facility and severity
    facility = random.choice(list(FACILITIES.keys()))
    severity = random.choice(list(SEVERITIES.keys()))
    
    # Get a random message for the selected facility
    message = random.choice(MESSAGES.get(facility, ['Generic system message']))
    
    # Generate timestamp
    timestamp = datetime.datetime.now().strftime('%b %d %H:%M:%S')
    
    # Generate hostname
    hostname = f'host-{random.randint(1, 5)}'
    
    # Format the syslog message
    priority = FACILITIES[facility] * 8 + SEVERITIES[severity]
    return f'<{priority}>{timestamp} {hostname} {message}'

def send_syslog_message(message, host='localhost', port=514):
    try:
        # Create UDP socket
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        
        # Try to resolve hostname, if it fails, use environment variable
        try:
            addr = socket.gethostbyname(host)
        except socket.gaierror:
            # If hostname resolution fails, try using the environment variable
            addr = os.environ.get('RSYSLOG_HOST', 'localhost')
        
        # Send message
        sock.sendto(message.encode(), (addr, port))
        
        # Close socket
        sock.close()
        
        return True
    except Exception as e:
        print(f"Error sending message: {e}")
        return False

def main():
    # Get command line arguments or use environment variables
    host = sys.argv[1] if len(sys.argv) > 1 else os.environ.get('RSYSLOG_HOST', 'localhost')
    port = int(sys.argv[2]) if len(sys.argv) > 2 else int(os.environ.get('RSYSLOG_PORT', '514'))
    
    print(f"Sending syslog messages to {host}:{port}")
    print("Press Ctrl+C to stop")
    
    try:
        while True:
            # Generate and send message
            message = generate_syslog_message()
            if send_syslog_message(message, host, port):
                print(f"Sent: {message}")
            
            # Wait between 0.1 and 1 second
            time.sleep(random.uniform(0.1, 1))
    except KeyboardInterrupt:
        print("\nStopping log simulation")

if __name__ == '__main__':
    main() 