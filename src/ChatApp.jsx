import React, { useState, useEffect, useRef } from 'react';
import './ChatApp.css';

function ChatApp() {
    const [messages, setMessages] = useState([]);
    const [inputText, setInputText] = useState('');
    const [username, setUsername] = useState('');
    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const socketRef = useRef(null);
    const messagesEndRef = useRef(null);

    // Conectar ao WebSocket
    const connectWebSocket = () => {
        if (socketRef.current) return; // Evita múltiplas conexões

        const ws = new WebSocket("ws://localhost:8080");

        ws.onopen = () => {
            console.log('Conectado ao servidor WebSocket');
            ws.send(JSON.stringify({ type: 'join', username }));
        };

        ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                setMessages((prevMessages) => [...prevMessages, data]);
            } catch (error) {
                console.error('Erro ao processar mensagem:', error);
            }
        };

        ws.onclose = () => {
            console.log('Desconectado. Tentando reconectar...');
            socketRef.current = null;
            setTimeout(connectWebSocket, 3000);
        };

        socketRef.current = ws;
    };

    useEffect(() => {
        if (!isLoggedIn) return;
        connectWebSocket();
    }, [isLoggedIn]);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    const handleLogin = (e) => {
        e.preventDefault();
        if (username.trim()) {
            setIsLoggedIn(true);
        }
    };

    const handleSendMessage = (e) => {
        e.preventDefault();
        if (inputText.trim() && socketRef.current) {
            const messageObj = JSON.stringify({
                type: 'message',
                username: username,
                message: inputText
            });

            console.log("Enviando mensagem para o servidor:", messageObj);
            socketRef.current.send(messageObj);
            setInputText('');
        }
    };

    if (!isLoggedIn) {
        return (
            <div className="login-container">
                <div className="login-box">
                    <h2>Chat em Tempo Real</h2>
                    <form onSubmit={handleLogin}>
                        <input
                            type="text"
                            placeholder="Digite seu nome"
                            value={username}
                            onChange={(e) => setUsername(e.target.value)}
                            required
                        />
                        <button type="submit">Entrar</button>
                    </form>
                </div>
            </div>
        );
    }

    return (
        <div className="chat-container">
            <div className="chat-header">
                <h2>Chat em Tempo Real</h2>
                <p>Conectado como: {username}</p>
            </div>

            <div className="messages-container">
                {messages.map((msg, index) => (
                    <div key={index} className={`message ${msg.username === username ? 'my-message' : 'other-message'}`}>
                        <div className="message-header">
                            <span className="username">{msg.username}</span>
                            <span className="timestamp">{msg.timestamp}</span>
                        </div>
                        <p>{msg.message}</p>
                    </div>
                ))}
                <div ref={messagesEndRef} />
            </div>

            <form className="message-form" onSubmit={handleSendMessage}>
                <input
                    type="text"
                    placeholder="Digite sua mensagem..."
                    value={inputText}
                    onChange={(e) => setInputText(e.target.value)}
                />
                <button type="submit">Enviar</button>
            </form>
        </div>
    );
}

export default ChatApp;
