import React, { useEffect, useState } from "react";
import {
  Paper,
  Typography,
  List,
  ListItem,
  ListItemText,
  CircularProgress,
  Box,
  Button,
  AppBar,
  Toolbar,
} from "@mui/material";

const HistoricoDeMensagens = ({ setTela }) => {
  const [mensagens, setMensagens] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("http://localhost/api/obterMensagens/")
      .then((res) => res.json())
      .then((data) => {
        setMensagens(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Erro ao buscar mensagens:", err);
        setLoading(false);
      });
  }, []);

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" mt={4}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box sx={{ maxWidth: 800, mx: "auto", mt: 4 }}>
      <AppBar position="static">
        <Toolbar sx={{ justifyContent: "space-between" }}>
          <Typography variant="h6">Histórico de Mensagens</Typography>
          <Button
            variant="outlined"
            color="inherit"
            onClick={() => setTela("chat")}
          >
            Voltar para o Chat
          </Button>
        </Toolbar>
      </AppBar>

      <Paper sx={{ p: 2, mt: 2, bgcolor: "grey.900" }}>
        <List sx={{ maxHeight: 500, overflowY: "auto" }}>
          {mensagens.map((msg) => (
            <ListItem key={msg.id} divider>
              <ListItemText
                primary={`${msg.username} — ${new Date(
                  msg.timestamp
                ).toLocaleString()}`}
                secondary={msg.message}
                primaryTypographyProps={{ color: "white" }}
                secondaryTypographyProps={{ color: "grey.400" }}
              />
            </ListItem>
          ))}
        </List>
      </Paper>
    </Box>
  );
};

export default HistoricoDeMensagens;
