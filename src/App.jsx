import React, { useState } from "react";
import ChatApp from "./ChatApp";
import HistoricoDeMensagens from "./Historico";

function App() {
  const [tela, setTela] = useState("chat"); // 'chat' ou 'historico'

  return (
    <div className="App">
      {tela === "chat" ? (
        <ChatApp setTela={setTela} />
      ) : (
        <HistoricoDeMensagens setTela={setTela} />
      )}
    </div>
  );
}

export default App;
