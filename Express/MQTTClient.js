// MQTTClient.js
const mqtt = require('mqtt');

class MQTTClient {
    constructor() {
        this.client = null;
    }

    connect() {
        if (!this.client) {
            this.client = mqtt.connect('mqtt://broker.hivemq.com');
            // Setup your event handlers here, e.g. on connect, on message
            this.client.on('connect', () => {
                console.log('MQTT Client Connected');
            });
            this.client.on('message', (topic, message) => {
                // handle incoming messages
            });
        }
        return this.client;
    }

    static getInstance() {
        if (!this.instance) {
            this.instance = new MQTTClient();
        }
        return this.instance;
    }

    // Other methods like subscribe, publish, etc.
    subscribe(topic) {
        this.connect().subscribe(topic);
    }

    publish(topic, message) {
        this.connect().publish(topic, message);
    }
}

module.exports = MQTTClient;
