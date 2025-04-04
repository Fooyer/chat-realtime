import React, { useState, useEffect, useRef } from "react";
import {
  Box,
  Typography,
  TextField,
  Button,
  Paper,
  List,
  ListItem,
  AppBar,
  Toolbar,
  Container,
} from "@mui/material";

function ChatApp({ setTela }) {
  const [messages, setMessages] = useState([]);
  const [inputText, setInputText] = useState("");
  const [username, setUsername] = useState("");
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const socketRef = useRef(null);
  const messagesEndRef = useRef(null);

  const connectWebSocket = () => {
    if (socketRef.current) return;

    const ws = new WebSocket("ws://localhost:8080");

    ws.onopen = () => {
      ws.send(JSON.stringify({ type: "join", username }));
    };

    ws.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        setMessages((prev) => [...prev, data]);
      } catch (error) {
        console.error("Erro ao processar mensagem:", error);
      }
    };

    ws.onclose = () => {
      socketRef.current = null;
      setTimeout(connectWebSocket, 3000);
    };

    socketRef.current = ws;
  };

  useEffect(() => {
    if (isLoggedIn) connectWebSocket();
  }, [isLoggedIn]);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
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
      socketRef.current.send(
        JSON.stringify({ type: "message", username, message: inputText })
      );
      setInputText("");
    }
  };

  if (!isLoggedIn) {
    return (
      <Container maxWidth="sm">
        <Paper sx={{ mt: 8, p: 4, textAlign: "center", bgcolor: "grey.900" }}>
          <Typography variant="h5" gutterBottom color="white">
            Chat em Tempo Real
          </Typography>
          <form onSubmit={handleLogin}>
            <TextField
              fullWidth
              label="Digite seu nome"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              sx={{ mb: 2 }}
              variant="filled"
              InputLabelProps={{ style: { color: "white" } }}
              InputProps={{ style: { color: "white" } }}
            />
            <Button fullWidth variant="contained" type="submit">
              Entrar
            </Button>
          </form>
        </Paper>
      </Container>
    );
  }

  return (
    <Box sx={{ maxWidth: 800, mx: "auto", mt: 4 }}>
      <AppBar position="static">
        <Toolbar sx={{ justifyContent: "space-between" }}>
          <Typography variant="h6">Chat em Tempo Real</Typography>
          <Box display="flex" alignItems="center" gap={2}>
            <Typography variant="body1">Conectado como: {username}</Typography>
            <Button
              variant="outlined"
              color="inherit"
              onClick={() => setTela("historico")}
            >
              Ver Histórico
            </Button>
          </Box>
        </Toolbar>
      </AppBar>

      <Paper
        sx={{
          p: 2,
          minHeight: 400,
          maxHeight: 500,
          overflowY: "auto",
          mt: 2,
          bgcolor: "grey.900",
        }}
      >
        <List>
          {messages.map((msg, index) => (
            <ListItem
              key={index}
              sx={{
                justifyContent:
                  msg.username === username ? "flex-end" : "flex-start",
              }}
            >
              <Box
                sx={{
                  bgcolor: msg.username === username ? "#1565c0" : "grey.800",
                  color: "white",
                  p: 1.5,
                  borderRadius: 2,
                  maxWidth: "70%",
                }}
              >
                <Typography variant="caption" fontWeight="bold">
                  {msg.username} — {msg.timestamp}
                </Typography>
                <Typography variant="body1">{msg.message}</Typography>
              </Box>
            </ListItem>
          ))}
        </List>
        <div ref={messagesEndRef} />
      </Paper>

      <Box
        component="form"
        onSubmit={handleSendMessage}
        sx={{ display: "flex", mt: 2, gap: 1 }}
      >
        <TextField
          fullWidth
          placeholder="Digite sua mensagem..."
          value={inputText}
          onChange={(e) => setInputText(e.target.value)}
          variant="filled"
          InputProps={{ style: { color: "white" } }}
        />
        <Button type="submit" variant="contained">
          Enviar
        </Button>
      </Box>
    </Box>
  );
}

export default ChatApp;
