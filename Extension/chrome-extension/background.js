chrome.runtime.onInstalled.addListener(() => {
    chrome.storage.sync.get(['interval'], ({ interval }) => {
        if (!interval) {
            chrome.storage.sync.set({ interval: 5 });
        }
    });
    setupAlarm();
});

chrome.alarms.onAlarm.addListener(() => {
    fetchData();
});

function setupAlarm() {
    chrome.storage.sync.get(['interval'], ({ interval }) => {
        chrome.alarms.create('checkEmails', {
            periodInMinutes: parseInt(interval || 5),
        });
    });
}

function fetchData() {
    chrome.storage.sync.get(['endpoint', 'token'], ({ endpoint, token }) => {
        if (!endpoint || !token) {
            return;
        }

        fetch(endpoint + '?action=get&token=' + token)
            .then((res) => {
                if (!res.ok) {
                    throw new Error('Network response was not ok: ' + res.statusText);
                }
                return res.json();
            })
            .then((emailData) => {
                chrome.storage.local.set({ emailData }, () => {
                    let totalUnread = Object.entries(emailData)
                        .filter(([key]) => key !== 'timestamp')
                        .reduce((sum, [, value]) => {
                            const num = parseInt(value);
                            return sum + (isNaN(num) ? 0 : num);
                        }, 0);

                    chrome.action.setBadgeText({ text: totalUnread > 0 ? String(totalUnread) : '' });
                    chrome.action.setBadgeBackgroundColor({ color: '#ff0000' });
                });
            })
            .catch((err) => {
                console.error('Failed to fetch email data:', err);
            });
    });
}

chrome.storage.onChanged.addListener((changes) => {
    if (changes.interval) {
        setupAlarm();
    }
});
