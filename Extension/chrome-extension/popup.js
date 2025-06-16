const emailsDiv = document.getElementById("emails");
const timestampDiv = document.getElementById("timestamp");
const errorBox = document.querySelector("#errorMessage");
const errorText = document.querySelector("#errorMessage #errorText");

document.querySelector("#refresh").addEventListener("click", () => {
    chrome.storage.sync.get(["endpoint", "token"], ({ endpoint, token }) => {
        if (!endpoint || !token) {
            showError("Endpoint or token is not set.");
            return;
        }

        fetch(`${endpoint}?action=update&token=${token}`)
            .then((res) => {
                if (!res.ok) {
                    throw new Error("Network response was not ok");
                }
                return res.json();
            })
            .then(() => {
                fetchData();
            })
            .catch((err) => {
                console.error("Error fetching data:", err);
                showError("Failed to refresh data.");
            });
    });
});

function fetchData() {
    chrome.storage.sync.get(["endpoint", "token"], ({ endpoint, token }) => {
        fetch(`${endpoint}?action=get&token=${token}`)
            .then((res) => {
                if (!res.ok) {
                    throw new Error("Network response was not ok");
                }
                return res.json();
            })
            .then((emailData) => {
                chrome.storage.local.set({ emailData }, () => {
                    renderEmails(emailData);

                    let totalUnread = Object.entries(emailData)
                        .filter(([key]) => key !== "timestamp")
                        .reduce((sum, [, value]) => {
                            const num = parseInt(value);
                            return sum + (isNaN(num) ? 0 : num);
                        }, 0);

                    chrome.action.setBadgeText({ text: totalUnread > 0 ? String(totalUnread) : "" });
                    chrome.action.setBadgeBackgroundColor({ color: "#ff0000" });
                });
            })
            .catch((err) => {
                console.error("Error fetching email data:", err);
                showError("Failed to fetch email data.");
            });
    });
}

function renderEmails(emailData) {
    if (!emailData) {
        showError("No email data found.");
        return;
    }

    errorBox.classList.add("d-none");
    emailsDiv.innerHTML = "";
    timestampDiv.textContent = "";

    Object.entries(emailData).forEach(([email, count]) => {
        if (email === "timestamp") {
            timestampDiv.textContent = `Last update: ${count}`;
        } else {
            const listItem = document.createElement("li");
            listItem.className = "list-group-item d-flex justify-content-between";

            const divContent = document.createElement("div");
            divContent.className = "fw-bold";
            divContent.textContent = email;

            const badge = document.createElement("span");
            badge.className = "badge bg-primary rounded-pill";
            badge.textContent = count;
            badge.style.marginTop = "5px";

            listItem.appendChild(divContent);
            listItem.appendChild(badge);
            emailsDiv.appendChild(listItem);
        }
    });
}

chrome.storage.local.get("emailData", ({ emailData }) => {
    renderEmails(emailData);
});

function showError(message) {
    errorBox.classList.remove("d-none");
    errorText.textContent = message;
    emailsDiv.innerHTML = "";
    timestampDiv.textContent = "";
}
