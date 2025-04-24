#!/usr/bin/env python3
import socket
import time
import random
import argparse
from datetime import datetime

# Sample log messages and their priorities
SAMPLE_LOGS = [
    # Priority 0 (Emergency)
    (0, [
        "EMERGENCY: System kernel panic detected",
        "EMERGENCY: Complete system failure imminent",
        "EMERGENCY: Critical security breach detected"
    ]),
    # Priority 1 (Alert)
    (1, [
        "ALERT: System memory critically low",
        "ALERT: Multiple failed login attempts detected",
        "ALERT: Database connection lost"
    ]),
    # Priority 2 (Critical)
    (2, [
        "CRITICAL: High CPU usage detected",
        "CRITICAL: Disk space nearly full",
        "CRITICAL: Service crash detected"
    ]),
    # Priority 3 (Error)
    (3, [
        "ERROR: Failed to connect to database",
        "ERROR: Service startup failed",
        "ERROR: Configuration file not found"
    ]),
    # Priority 4 (Warning)
    (4, [
        "WARNING: High memory usage detected",
        "WARNING: Slow database query performance",
        "WARNING: Service response time degraded"
    ]),
    # Priority 5 (Notice)
    (5, [
        "NOTICE: Service restarted successfully",
        "NOTICE: Backup process completed",
        "NOTICE: System update available"
    ]),
    # Priority 6 (Info)
    (6, [
        "INFO: User logged in successfully",
        "INFO: Scheduled maintenance started",
        "INFO: Service health check passed"
    ]),
    # Priority 7 (Debug)
    (7, [
        "DEBUG: Query execution time: 1.2s",
        "DEBUG: Cache hit ratio: 85%",
        "DEBUG: Memory allocation details"
    ])
]

def generate_syslog_message(facility=1):
    """Generate a random syslog message with priority"""
    priority, messages = random.choice(SAMPLE_LOGS)
    message = random.choice(messages)
    
    # Calculate PRI value (facility * 8 + priority)
    pri = facility * 8 + priority
    
    # Generate timestamp
    timestamp = datetime.now().strftime('%b %d %H:%M:%S')
    
    # Generate hostname
    hostname = f"host{random.randint(1,5)}.local"
    
    # Generate tag
    tag = "LogSimulator"
    
    # Format the full message
    return f"<{pri}>{timestamp} {hostname} {tag}: {message}"

def send_syslog(message, host='127.0.0.1', port=514):
    """Send a syslog message to the specified host and port"""
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.sendto(message.encode(), (host, port))
    sock.close()

def main():
    parser = argparse.ArgumentParser(description='Syslog Message Simulator')
    parser.add_argument('--host', default='127.0.0.1', help='Syslog server host (default: 127.0.0.1)')
    parser.add_argument('--port', type=int, default=514, help='Syslog server port (default: 514)')
    parser.add_argument('--interval', type=float, default=1.0, help='Interval between messages in seconds (default: 1.0)')
    parser.add_argument('--count', type=int, default=0, help='Number of messages to send (0 for infinite, default: 0)')
    
    args = parser.parse_args()
    
    print(f"Starting syslog simulator...")
    print(f"Sending messages to {args.host}:{args.port}")
    print(f"Interval: {args.interval} seconds")
    print(f"Count: {'infinite' if args.count == 0 else args.count}")
    print("Press Ctrl+C to stop")
    
    try:
        count = 0
        while args.count == 0 or count < args.count:
            message = generate_syslog_message()
            send_syslog(message, args.host, args.port)
            print(f"Sent: {message}")
            time.sleep(args.interval)
            if args.count > 0:
                count += 1
    except KeyboardInterrupt:
        print("\nStopping syslog simulator...")

if __name__ == "__main__":
    main() 