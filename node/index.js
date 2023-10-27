/*
 _   _              _       ____             
| \ | | ___ _ __ __| |_   _|  _ \  _____   __
|  \| |/ _ \ '__/ _` | | | | | | |/ _ \ \ / /
| |\  |  __/ | | (_| | |_| | |_| |  __/\ V / 
|_| \_|\___|_|  \__,_|\__, |____/ \___| \_/  
                      |___/                  
  ___  _      __                     _                __   _ ______  
 / _ \| |__  / _|_   _ ___  ___ __ _| |_ ___  _ __   / /  | / ___\ \ 
| | | | '_ \| |_| | | / __|/ __/ _` | __/ _ \| '__| | |_  | \___ \| |
| |_| | |_) |  _| |_| \__ \ (_| (_| | || (_) | |    | | |_| |___) | |
 \___/|_.__/|_|  \__,_|___/\___\__,_|\__\___/|_|    | |\___/|____/| |
                                                     \_\         /_/ 
 */

const express = require("express");
const JavaScriptObfuscator = require("javascript-obfuscator");

const app = express();
const port = 8080;

app.use(express.json());

app.post("/obfuscate", (req, res) => {
  try {
    const { code } = req.body;

    if (!code || code == "") {
      return res
        .status(200)
        .json({ status: false, message: "Code cannot be empty" });
    }

    var obfuscationResult = JavaScriptObfuscator.obfuscate(code, {
      compact: false,
      controlFlowFlattening: true,
      controlFlowFlatteningThreshold: 1,
      numbersToExpressions: true,
      simplify: true,
      stringArrayShuffle: true,
      splitStrings: true,
      stringArrayThreshold: 1,
    });

    const obfuscatedFinalResult = obfuscationResult.getObfuscatedCode();

    return res.status(200).json({
      status: true,
      message: "done",
      data: { code: obfuscatedFinalResult },
    });
  } catch (e) {
    return res.status(500).json({ status: false, message: e.message });
  }
});

app.listen(port, () => console.log(`Express app running on port ${port}!`));
