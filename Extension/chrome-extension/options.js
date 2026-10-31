const endpoint = document.querySelector('#endpoint');
const token = document.querySelector('#token');
const interval = document.querySelector('#interval');
const saveMessage = document.querySelector('#saveMessage');

chrome.storage.sync.get(['endpoint', 'token', 'interval'], (data) => {
    endpoint.value = data.endpoint || '';
    token.value = data.token || '';
    interval.value = data.interval || 5;
});

document.querySelector('#save').addEventListener('click', () => {
    saveMessage.classList.add('d-none');

    const minutes = Math.max(1, parseInt(interval.value) || 5);
    interval.value = minutes;

    chrome.storage.sync.set(
        {
            endpoint: endpoint.value,
            token: token.value,
            interval: minutes,
        },
        () => {
            setTimeout(() => {
                saveMessage.classList.remove('d-none');
            }, 200);
        }
    );
});

document.querySelector('#close').addEventListener('click', () => {
    window.history.back();
});
