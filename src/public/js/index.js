// function updateClock() {
//     const now = new Date();
//     const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
//     const day = dayOfWeek[now.getDay()];
//     const date = now.getFullYear() + '年' + String(now.getMonth() + 1) + '月' + String(now.getDate()) + '日' + '(' + day + ')';
//     document.getElementById('date').textContent = date;

//     const time = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
//     document.getElementById('time').textContent = time;
// }

function updateClock() {
    const now = new Date();

    const dateOptions = { timeZone: 'Asia/Tokyo', year: 'numeric', month: 'numeric', day: 'numeric', weekday: 'short' };
    const timeOptions = { timeZone: 'Asia/Tokyo', hour: '2-digit', minute: '2-digit', hour12: false };

    const date = now.toLocaleDateString('ja-JP', dateOptions); 
    const time = now.toLocaleTimeString('ja-JP', timeOptions); 

    const match = date.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})\((.)\)$/);
    let formattedDate = date;
    if (match) {
        formattedDate = `${match[1]}年${match[2]}月${match[3]}日(${match[4]})`;
    }

    document.getElementById('date').textContent = formattedDate;
    document.getElementById('time').textContent = time;
}



setInterval(updateClock, 1000); // 1秒ごとに更新
updateClock(); // 初期表示

