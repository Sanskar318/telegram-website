const express = require("express");
const fs = require("fs");
const path = require("path");

const app = express();

/* ✅ CORS FIX (VERY IMPORTANT) */
app.use((req, res, next) => {
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET,POST");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type");
  next();
});

app.use(express.json());

const DATA_FILE = path.join(__dirname, "data.json");

/* ensure file exists */
if (!fs.existsSync(DATA_FILE)) {
  fs.writeFileSync(DATA_FILE, "[]");
}

/* webhook */
app.post("/webhook", (req, res) => {
  try {
    const msg = req.body.message;
    if (!msg || !msg.text) return res.sendStatus(200);

    const data = JSON.parse(fs.readFileSync(DATA_FILE));

    data.push({
      text: msg.text,
      date: new Date().toISOString(),
      link: `https://t.me/${process.env.CHANNEL}`
    });

    fs.writeFileSync(DATA_FILE, JSON.stringify(data, null, 2));
    res.sendStatus(200);
  } catch (e) {
    console.log(e);
    res.sendStatus(500);
  }
});

/* PUBLIC API */
app.get("/data", (req, res) => {
  res.setHeader("Content-Type", "application/json");
  const data = fs.readFileSync(DATA_FILE);
  res.send(data);
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log("Server running on", PORT));
