const express = require("express");
const fs = require("fs");
const path = require("path");

const app = express();

/* 🔓 CORS */
app.use((req, res, next) => {
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET,POST");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type");
  next();
});

app.use(express.json());

const DATA_FILE = path.join(__dirname, "posts.json");

/* Ensure file exists */
if (!fs.existsSync(DATA_FILE)) {
  fs.writeFileSync(DATA_FILE, JSON.stringify([]));
}

/* 🔔 TELEGRAM WEBHOOK */
app.post("/webhook", (req, res) => {
  try {
    const msg = req.body.channel_post;
    if (!msg) return res.sendStatus(200);

    const posts = JSON.parse(fs.readFileSync(DATA_FILE));

    let media = null;
    let type = "text";

    if (msg.photo) {
      const photo = msg.photo[msg.photo.length - 1];
      media = `https://api.telegram.org/file/bot${process.env.BOT_TOKEN}/${photo.file_id}`;
      type = "image";
    }

    if (msg.video) {
      media = `https://api.telegram.org/file/bot${process.env.BOT_TOKEN}/${msg.video.file_id}`;
      type = "video";
    }

    posts.push({
      text: msg.text || msg.caption || "",
      media,
      type,
      date: new Date().toISOString(),
      link: `https://t.me/${process.env.CHANNEL}/${msg.message_id}`
    });

    if (posts.length > 50) posts.shift();

    fs.writeFileSync(DATA_FILE, JSON.stringify(posts, null, 2));
    res.sendStatus(200);
  } catch (err) {
    console.error(err);
    res.sendStatus(500);
  }
});

/* 🌐 API */
app.get("/data", (req, res) => {
  const posts = JSON.parse(fs.readFileSync(DATA_FILE));
  res.json(posts.slice().reverse());
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log("Server running on", PORT);
});
