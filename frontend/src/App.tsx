import { useState } from "react";

import "./App.css";

import logo from "./assets/logo.png";

import php from "./assets/php.png";
import python from "./assets/python.png";
import js from "./assets/js.png";

import Loader from "./Loader";

import zipIcon from "./assets/zip.png";
import fileIcon from "./assets/file.png";

const App = () => {
  const year = new Date().getFullYear();

  const [currentTab, setCurrentTab] = useState<"one" | "two">("one");
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const introFns = [
    '// Sample JavaScript code\n\nfunction hi() {\n\tconsole.log("Hello World!");\n}\n\nhi();',
    '// Sample PHP code\n\n<?php\n\tfunction hi() {\n\t\techo "Hello World!";\n\t}\n\n\thi();\n?>',
    '// Sample Python code\n\ndef hi():\n\tprint("Hello World!")\n\nhi()',
  ];

  const submitForm = (e: any) => {
    e.preventDefault();

    setIsLoading(true);
  };

  return (
    <main className="container">
      <section>
        <div className="header d-flex">
          <div>
            <img src={logo} alt="anonymous" />
          </div>
          <div>
            <h1>Source Code Obfuscator</h1>
            <hr />
            <p>
              A free and efficient obfuscator. Make your code harder to copy and
              prevent people from stealing your work. This tool is a Web UI to
              the excellent created and designed by&nbsp;
              <strong>~NerdyDev 🥀</strong>
            </p>
          </div>
        </div>

        <div className="supported-languages d-flex align-center">
          <p>Supports</p>
          <span>|</span>
          <div>
            <img src={php} alt="php" />
            <img src={python} alt="python" />
            <img src={js} alt="js" />
          </div>
        </div>

        <div className="upload-box">
          <div className="tabs">
            <p
              className={currentTab === "one" ? "active" : ""}
              onClick={() => setCurrentTab("one")}
            >
              Copy & Paste Code
            </p>
            <p
              className={currentTab === "two" ? "active" : ""}
              onClick={() => setCurrentTab("two")}
            >
              Upload Project
            </p>
          </div>

          <div
            className={`code-block ${
              currentTab === "one" ? "d-block" : "d-none"
            }`}
          >
            <form action="#" onSubmit={submitForm}>
              <select name="language" id="language" defaultValue="">
                <option value="" disabled>
                  --choose language--
                </option>
                <option value="js">JavaScript</option>
                <option value="php">PHP</option>
                <option value="python">Python</option>
              </select>
              <textarea
                name="code"
                id="code"
                rows={10}
                placeholder="Your code here!!"
                defaultValue={
                  introFns[Math.floor(Math.random() * introFns.length + 0)]
                }
              />
              <div className="d-flex align-center gap-1">
                <button disabled={isLoading}>Obfuscate</button>
                {isLoading && <Loader color="#555" />}
              </div>
            </form>
          </div>

          <div
            className={`upload-block ${
              currentTab === "two" ? "d-block" : "d-none"
            }`}
          >
            <div>
              <p>You can upload a Zip file or a single executable file</p>
              <div>
                <img src={zipIcon} alt="zip" />
                <div>
                  <hr />
                  <span>or</span>
                  <hr />
                </div>
                <img src={fileIcon} alt="file" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <footer>
        <hr />
        <p>
          Copyright &copy; <strong>{year}</strong>
        </p>
      </footer>
    </main>
  );
};

export default App;
