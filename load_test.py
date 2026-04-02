"""
MQTT Load Test Script — IoT Dashboard Dissertation
Publishes simulated device messages at a controlled rate.

Usage:
  python load_test.py --rate 50 --duration 60 --qos 0
  python load_test.py --rate 100 --duration 60 --qos 1

Requirements:
  pip install paho-mqtt
"""

import argparse
import json
import time
import sys

try:
    import paho.mqtt.client as mqtt
    from paho.mqtt.enums import CallbackAPIVersion
except ImportError:
    print("ERROR: paho-mqtt not installed. Run: pip install paho-mqtt")
    sys.exit(1)

parser = argparse.ArgumentParser(description='MQTT Load Test for IoT Dashboard')
parser.add_argument('--host',     default='localhost', help='MQTT broker host (default: localhost)')
parser.add_argument('--port',     type=int, default=1883, help='MQTT broker port (default: 1883)')
parser.add_argument('--rate',     type=int, default=10,   help='Target messages per second (default: 10)')
parser.add_argument('--duration', type=int, default=60,   help='Test duration in seconds (default: 60)')
parser.add_argument('--devices',  type=int, default=5,    help='Number of simulated devices (default: 5)')
parser.add_argument('--qos',      type=int, choices=[0, 1, 2], default=0, help='MQTT QoS level (default: 0)')
args = parser.parse_args()

# Connect to broker
client = mqtt.Client(CallbackAPIVersion.VERSION2, client_id="iot_load_test_publisher")
try:
    client.connect(args.host, args.port)
except Exception as e:
    print(f"ERROR: Could not connect to broker at {args.host}:{args.port}")
    print(f"       {e}")
    sys.exit(1)

client.loop_start()

interval      = 1.0 / args.rate
sent          = 0
target_total  = args.rate * args.duration

print("=" * 50)
print("  MQTT Load Test")
print("=" * 50)
print(f"  Broker:   {args.host}:{args.port}")
print(f"  Rate:     {args.rate} msg/s")
print(f"  Duration: {args.duration}s")
print(f"  Devices:  {args.devices}")
print(f"  QoS:      {args.qos}")
print(f"  Target:   {target_total} messages")
print("=" * 50)

start_time = time.time()

try:
    while time.time() - start_time < args.duration:
        loop_start = time.time()

        device_num = (sent % args.devices) + 1
        device_id  = f"device_{device_num}"

        payload = json.dumps({
            "state":       "on" if sent % 2 == 0 else "off",
            "temperature": round(18.0 + (device_num * 1.5) + ((sent // args.devices) % 10) * 0.2, 1),
            "humidity":    round(40.0 + (device_num * 3.0) % 20, 1),
            "value":       sent,
            "_test_ts":    time.time(),  # publish timestamp — used by listener to calculate latency
        })

        # Topic: loadtest/{device_id}
        # The listener's generic 2-segment fallback maps this to:
        #   entity_type = "loadtest", entity_id = device_id, attribute = "payload"
        client.publish(f"loadtest/{device_id}", payload, qos=args.qos)
        sent += 1

        if sent % max(1, args.rate * 5) == 0:
            elapsed     = time.time() - start_time
            actual_rate = sent / elapsed
            remaining   = args.duration - elapsed
            
            print(f"  [{elapsed:5.1f}s] {sent:>6} sent  |  {actual_rate:6.1f} msg/s actual  |  {remaining:.0f}s remaining")

        # Sleep for the remainder of this interval to hit the target rate
        sleep_time = interval - (time.time() - loop_start)
        if sleep_time > 0:
            time.sleep(sleep_time)

except KeyboardInterrupt:
    print("\n  Test interrupted by user")

elapsed     = time.time() - start_time
actual_rate = sent / elapsed if elapsed > 0 else 0
efficiency  = min(100.0, actual_rate / args.rate * 100)

print("=" * 50)
print("  Results")
print("=" * 50)
print(f"  Messages sent:  {sent}")
print(f"  Duration:       {elapsed:.2f}s")
print(f"  Actual rate:    {actual_rate:.2f} msg/s")
print(f"  Target rate:    {args.rate} msg/s")
print(f"  Efficiency:     {efficiency:.1f}%")
print(f"  QoS level:      {args.qos}")
print("=" * 50)
print("  Check the Stats page to see events received and latency.")
print("=" * 50)

client.loop_stop()
client.disconnect()
