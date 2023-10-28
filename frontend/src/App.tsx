/*
 _   _              _       ____             
| \ | | ___ _ __ __| |_   _|  _ \  _____   __
|  \| |/ _ \ '__/ _` | | | | | | |/ _ \ \ / /
| |\  |  __/ | | (_| | |_| | |_| |  __/\ V / 
|_| \_|\___|_|  \__,_|\__, |____/ \___| \_/  
                      |___/                  
  ___  _      __                     _             
 / _ \| |__  / _|_   _ ___  ___ __ _| |_ ___  _ __ 
| | | | '_ \| |_| | | / __|/ __/ _` | __/ _ \| '__|
| |_| | |_) |  _| |_| \__ \ (_| (_| | || (_) | |   
 \___/|_.__/|_|  \__,_|___/\___\__,_|\__\___/|_| 
 */

import { useState, useRef } from "react";

import "./App.css";

import logo from "./assets/logo.png";

import php from "./assets/php.png";
import js from "./assets/js.png";
import html from "./assets/html.png";
import css from "./assets/css.png";

import github from "./assets/github.png";
import linkedin from "./assets/linkedin.png";
import x from "./assets/twitter.png";

import Loader from "./Loader";

import zipIcon from "./assets/zip.png";
import fileIcon from "./assets/file.png";
import loaderImage from "./assets/loader.gif";

const { VITE_API_ENDPOINT } = import.meta.env;

if (!VITE_API_ENDPOINT) {
  throw new Error(".env variable missing: VITE_API_ENDPOINT");
}

const maxFileSize = 7 * 1024 * 1024;

const App = () => {
  const year = new Date().getFullYear();

  const [currentTab, setCurrentTab] = useState<"one" | "two">("one");
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [isDownloading, setIsDownloading] = useState<boolean>(false);
  const [fileURL, setFileURL] = useState<string | null>(null);
  const [displayOverlay, setDisplayOverlay] = useState<boolean>(false);

  const fileInputRef = useRef<any>(null);

  const introFns = [
    '// Sample JavaScript code\n\nfunction hi() {\n\tconsole.log("Hello World!");\n}\n\nhi();',
    '// Sample PHP code\n\n<?php\n\tfunction hi() {\n\t\techo "Hello World!";\n\t}\n\n\thi();\n?>',
  ];

  const serializeForm = (form: HTMLFormElement) => {
    const formData: Record<string, string> = {};
    const elements = form.elements;

    for (let i = 0; i < elements.length; i++) {
      const element = elements[i] as HTMLInputElement;
      const name = element.name;

      if (name) {
        formData[name] = element.value;
      }
    }

    return formData;
  };

  const obfuscateCode = async (e: any) => {
    e.preventDefault();

    setIsLoading(true);

    const { language, code } = serializeForm(e.target);

    const formData = new FormData();
    formData.append("obfuscate", "true");
    formData.append("language", language);
    formData.append("code", `${code}`);

    try {
      const response = await fetch(VITE_API_ENDPOINT, {
        method: "POST",
        body: formData,
      });

      if (response.ok) {
        const result = await response.json();

        if (result.status) setFileURL(result.data.url);
        else alert(result.message ?? "Unable to complete request");
      } else {
        alert("Some issues occured!");
      }
    } catch (e: any) {
      console.error("Network error:", e);
      alert("Network error! Check console for more details");
    } finally {
      setIsLoading(false);
    }

    return;
  };

  const handleDrop = async (e: any) => {
    e.preventDefault();

    setDisplayOverlay(false);

    const file = e.dataTransfer.files[0];

    await uploadFile(file);
  };

  const uploadFile = async (file: File) => {
    if (file.size > maxFileSize) {
      alert("File size should not be more than 7MB");
      return;
    }

    const formData = new FormData();
    formData.append("obfuscate", "true");
    formData.append("file", file);

    setIsLoading(true);

    try {
      const response = await fetch(VITE_API_ENDPOINT, {
        method: "POST",
        body: formData,
      });

      if (response.ok) {
        const result = await response.json();

        if (result.status) setFileURL(result.data.url);
        else alert(result.message ?? "Unable to complete request");
      } else {
        alert("Some issues occured!");
      }
    } catch (e: any) {
      console.error("Network error:", e);
      alert("Network error! Check console for more details");
    } finally {
      setIsLoading(false);
    }
  };

  const triggerImageUpload = () => fileInputRef.current.click();

  const handleFileChange = async (event: any) => {
    const selectedFile = event.target.files[0];

    await uploadFile(selectedFile);
  };

  const downloadFile = async () => {
    if (!fileURL) return;

    setIsDownloading(true);

    try {
      const response = await fetch(fileURL);
      const blob = await response.blob();

      const url = window.URL.createObjectURL(blob);

      const a = document.createElement("a");
      a.style.display = "none";
      a.href = url;

      let spilttedURL = fileURL.split("/");
      a.download = spilttedURL[spilttedURL.length - 1];

      document.body.appendChild(a);
      a.click();

      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (e) {
      console.error("Unable to download file: ", e);
    } finally {
      setIsDownloading(false);
    }

    return;
  };

  return (
    <main className="container">
      {fileURL ? (
        <section className="download-area">
          <div className="back-btn" onClick={() => setFileURL(null)}>
            <div>&laquo;</div>
            <p>Back</p>
          </div>
          <div>
            <img src={logo} alt="anonymous" />
            <h1>
              Your <span>Obfuscated</span> file is ready!
            </h1>
            <p className="info">
              Hurray! You can now click on the button below to download your
              file. Let us know if you love it or not!
            </p>
            <button onClick={downloadFile} disabled={isDownloading}>
              {isDownloading ? (
                <div className="d-flex align-center gap-1">
                  <Loader width={24} height={24} />
                  <span>...</span>
                </div>
              ) : (
                "Download"
              )}
            </button>
          </div>
        </section>
      ) : (
        <section>
          <div className="header d-flex">
            <div>
              <img src={logo} alt="anonymous" />
            </div>
            <div>
              <h1>Source Code Obfuscator</h1>
              <hr />
              <p>
                A free and efficient obfuscator. Make your code harder to copy
                and prevent people from stealing your work. This tool is a Web
                UI to the excellent created and designed by&nbsp;
                <strong>~NerdyDev 🥀</strong>
              </p>
            </div>
          </div>

          <div className="supported-languages d-flex align-center">
            <p>Supports</p>
            <span>|</span>
            <div>
              <img src={php} alt="php" />
              <img src={js} alt="js" />
              <img src={css} alt="css" />
              <img src={html} alt="html" />
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
              <form action="#" onSubmit={obfuscateCode}>
                <select name="language" id="language" defaultValue="" required>
                  <option value="" disabled>
                    --choose language--
                  </option>
                  <option value="js">JavaScript</option>
                  <option value="php">PHP</option>
                  <option value="html">HTML</option>
                  <option value="css">CSS</option>
                </select>
                {/* <textarea
                  name="code"
                  id="code"
                  rows={10}
                  placeholder="Your code here!!"
                  defaultValue={
                    introFns[Math.floor(Math.random() * introFns.length + 0)]
                  }
                  required
                /> */}
                <textarea
                  name="code"
                  id="code"
                  rows={10}
                  placeholder="Your code here!!"
                  defaultValue={introFns[1]}
                  required
                />
                <div className="d-flex align-center gap-1">
                  <button type="submit" disabled={isLoading}>
                    Obfuscate
                  </button>
                  {isLoading && <Loader color="#555" />}
                </div>
              </form>
            </div>

            <div
              className={`upload-block ${
                currentTab === "two" ? "d-block" : "d-none"
              }`}
            >
              {isLoading ? (
                <div className="upload-loader">
                  <img src={loaderImage} alt="loading" />
                  <p>Hang on please. Might take some while!</p>
                </div>
              ) : (
                <>
                  <input
                    type="file"
                    name="file"
                    accept=".zip, .html, .css, .php, .js"
                    style={{ display: "none" }}
                    ref={fileInputRef}
                    onChange={handleFileChange}
                  />
                  <div
                    onDrop={handleDrop}
                    onDragOver={(e) => {
                      e.preventDefault();
                      e.stopPropagation();
                      setDisplayOverlay(true);
                    }}
                    onDragLeave={() => setDisplayOverlay(false)}
                    onClick={triggerImageUpload}
                  >
                    {displayOverlay && (
                      <div className="upload-overlay">Drop it ASAP!</div>
                    )}
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
                    <p>Click to upload or drag & drop file</p>
                  </div>
                </>
              )}
            </div>
          </div>
        </section>
      )}

      <footer>
        <div className="d-flex align-center">
          <p>
            Copyright &copy; <strong>{year}</strong>
          </p>
          <div>
            <a href="https://github.com/iamthe-nerdyDev" target="_blank">
              <img src={github} alt="github" />
            </a>
            <a href="https://twitter.com/iamthe_nerdyDev" target="_blank">
              <img src={x} alt="x" />
            </a>
            <a
              href="https://www.linkedin.com/in/adedeji-morifeoluwa-0aa6361ba"
              target="_blank"
            >
              <img src={linkedin} alt="linkedin" />
            </a>
          </div>
        </div>
      </footer>
    </main>
  );
};

export default App;
