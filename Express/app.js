const express = require('express')
const mqtt = require('mqtt');
const axios = require('axios');

const app = express()
const port = 3000

app.get('/', (req, res) => {
    res.send('Hello World!')
})
app.get('/mqtt/general', (req, res) => {
    const data = req.query;
    console.log(data.payload)
    publishMessage(data.device_id, generateHexPayload(data.payload.command, data.payload.payload))
    res.send('test')
})

app.listen(port, () => {
})
const generalTopic = 'Lift/+/events/general';
const heartbeatTopic = 'Lift/+/events/heartbeat';
// Testmqtt.eideas.io
const client = mqtt.connect('mqtt://167.235.25.45', {
    port: 1883
});
app.get('/mqtt/general/force', (req, res) => {
    const data = req.query;
    publishMessage(data.device_id, data.payload)
    res.send('test')
})
client.on('connect', () => {
    // Once connected, subscribe to the topics
    client.subscribe([generalTopic, heartbeatTopic], () => {
    });
});

client.on('error', function (error) {
    console.error('Connection to HiveMQ broker failed:', error);
});

client.on('message', (topic, message) => {
    // Convert message to string and parse if necessary
    const msgJson = parseHexPayload(message);
 
    if (topic.match(/Lift\/[^\/]+\/events\/general/)) {
        if (msgJson.command === 1) {

        }
        if (msgJson.command === 4) {
            const payload = Buffer.from(msgJson.payload, 'binary');
            msgJson.amount = payload.readUInt16BE(0)
            msgJson.card = payload.toString('utf8', 2, 10)

        }
        axios.get('https://testmqtt.eideas.io/api/mqtt/general', {
            params: {
                payload: msgJson,
                topic: topic
            }
        })
            .then(response => {
            })
            .catch(error => console.error('Error sending general event', error));
    } else if (topic.match(/Lift\/[^\/]+\/events\/heartbeat/)) {
        axios.get('https://testmqtt.eideas.io/api/mqtt/heartbeat', {
            params: {
                payload: msgJson,
                topic: topic
            }
            // old
        })
            .then(response => { })
            .catch(error => console.error('Error sending heartbeat event', error));
    }
});
function parseHexPayload(byteString) {
    // Assuming 'byteString' is a Node.js Buffer
    const data = {
        timestamp: byteString.readUInt32BE(0),
        command: byteString.readUInt8(4),
        length: byteString.readUInt8(5)
    };

    // Extract payload using the length (starting from byte 6)
    const payload = byteString.slice(6, 6 + data.length);

    return {
        timestamp: data.timestamp,
        command: data.command,
        length: data.length,
        payload: payload
    };
}
function generateHexPayload(command, payload = []) {
    const commandBuffer = Buffer.alloc(5); // 4 bytes for timestamp, 1 byte for command
    commandBuffer.writeUInt32LE(Math.floor(Date.now() / 1000), 0); // Write timestamp
    commandBuffer.writeUInt8(command, 4); // Write command

    let payloadBufferList = [];

    for (const key in payload) {
        const item = payload[key];
        switch (item.type) {
            case 'string':
                payloadBufferList.push(Buffer.from(item.value, 'utf8'));
                break;
            case 'timestamp':
                const timeBuffer = Buffer.alloc(4);
                timeBuffer.writeUInt32LE(item.value, 0);
                payloadBufferList.push(timeBuffer);
                break;
            case 'number':
                const numBuffer = Buffer.alloc(1);
                numBuffer.writeUInt8(item.value, 0);
                payloadBufferList.push(numBuffer);
                break;
            case 'number16':
                const num16Buffer = Buffer.alloc(2);
                num16Buffer.writeUInt16LE(item.value, 0);
                payloadBufferList.push(num16Buffer);
                break;
            case 'number32':
                const num32Buf = Buffer.alloc(4);
                num32Buf.writeUInt32LE(item.value, 0);
                payloadBufferList.push(num32Buf);
                break;

        }
    }

    const payloadSize = Buffer.alloc(1);
    const totalPayloadLength = payloadBufferList.reduce((acc, curr) => acc + curr.length, 0);
    payloadSize.writeUInt8(totalPayloadLength, 0);
    payloadBufferList.unshift(payloadSize);

    return Buffer.concat([commandBuffer, ...payloadBufferList]);
}
function publishMessage(device_id, payload) {
    const topic = `Lift/${device_id}/commands/general`;
    console.log(topic)
    console.log(payload)
    client.publish(topic, payload, { qos: 1 }, (err) => {
        if (err) {
            console.log(err)
        } else {
            console.log(topic)
        }
    });
}