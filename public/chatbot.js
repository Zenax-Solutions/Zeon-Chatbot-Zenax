(function () {
    const script = document.currentScript;
    const iframe = document.createElement("iframe");
    const isMobile = window.innerWidth <= 768;

    const scriptUrl = new URL(script.src);
    const params = new URLSearchParams(scriptUrl.search);

    const allowedWebsite = params.get("website")?.replace(/\/$/, "");
    const currentWebsite = window.location.origin.replace(/\/$/, "");

    if (!allowedWebsite || allowedWebsite !== currentWebsite) {
        console.warn("Chatbot widget blocked due to domain mismatch.");
        return;
    }

    params.set("website", encodeURIComponent(allowedWebsite));
    const iframeSrc = `http://localhost:8004/embed/chatbot?${params.toString()}`;

    // 👇 Check if the iframe target exists first
    fetch(iframeSrc, { method: "HEAD" })
        .then((res) => {
            if (!res.ok) {
                console.warn(
                    "Chatbot iframe endpoint returned 404. Widget hidden."
                );
                return;
            }

            // ✅ Only inject if the page exists
            iframe.style.zIndex = "9999";
            iframe.src = iframeSrc;
            iframe.style.position = "fixed";
            iframe.style.bottom = "0px";
            iframe.style.right = "0px";
            iframe.style.width = isMobile ? "100%" : "480px";
            iframe.style.border = "none";
            iframe.style.background = "transparent";
            iframe.style.minHeight = "90vh";
            iframe.style.minWidth = "100%";
            iframe.style.overflow = "auto";
            iframe.style.display = "none";
            iframe.style.transition = "all 0.3s ease";
            iframe.setAttribute("allowtransparency", "true");
            iframe.setAttribute("allow", "clipboard-write");
            iframe.setAttribute("scrolling", "yes");

            document.body.appendChild(iframe);

            const lottieScript = document.createElement("script");
            lottieScript.type = "module";
            lottieScript.src =
                "https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs";
            document.body.appendChild(lottieScript);

            const buttonWrapper = document.createElement("div");
            buttonWrapper.style.position = "fixed";
            buttonWrapper.style.bottom = "20px";
            buttonWrapper.style.right = "20px";
            buttonWrapper.style.zIndex = "10000";
            buttonWrapper.style.cursor = "pointer";
            buttonWrapper.style.display = "flex";
            buttonWrapper.style.alignItems = "center";
            buttonWrapper.style.gap = "10px";
            buttonWrapper.style.background =
                "linear-gradient(to right, #60a5fa, #0284c7)";
            buttonWrapper.style.color = "#fff";
            buttonWrapper.style.padding = "10px 15px";
            buttonWrapper.style.borderRadius = "20px";
            buttonWrapper.style.boxShadow = "0 4px 12px rgba(0,0,0,0.2)";
            buttonWrapper.style.fontFamily = "sans-serif";

            const textDiv = document.createElement("div");
            textDiv.innerHTML = `
                <div style="font-size: 10px; line-height: 1;">Chat with</div>
                <div style="font-size: 18px; font-weight: bold; margin-top: -2px;">Zeon</div>
            `;

            const lottie = document.createElement("dotlottie-player");
            lottie.setAttribute(
                "src",
                "https://lottie.host/94673f52-5ca9-4fa5-a6c6-7bceef4c3668/aA2rFgI1Lj.lottie"
            );
            lottie.setAttribute("background", "transparent");
            lottie.setAttribute("speed", "1");
            lottie.setAttribute("style", "width: 60px; height: 60px");
            lottie.setAttribute("loop", "");
            lottie.setAttribute("autoplay", "");

            buttonWrapper.appendChild(textDiv);
            buttonWrapper.appendChild(lottie);
            document.body.appendChild(buttonWrapper);

            let isOpen = false;
            buttonWrapper.addEventListener("click", () => {
                isOpen = !isOpen;
                iframe.style.display = isOpen ? "block" : "none";

                if (isOpen) {
                    const audio = new Audio(
                        "http://localhost:8004/sounds/popup.mp3"
                    );
                    audio
                        .play()
                        .catch((e) =>
                            console.error("Widget open sound failed:", e)
                        );
                }
            });
        })
        .catch((err) => {
            console.error("Failed to verify chatbot endpoint:", err);
        });
})();
