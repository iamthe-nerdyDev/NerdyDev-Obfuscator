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
