function sendSearchBox(searchWord) {
    //console.log(searchWord);
    const inputElement = document.getElementById("searchBox");
    inputElement.value = searchWord;
    filterRows();
}
const inputElement = document.getElementById("searchBox");
const clearBtn = document.querySelector('.clear-btn');

// Xボタンをクリックしたときの処理
clearBtn.addEventListener('click', () => {
    inputElement.value = ''; // 文字列を空にする
    inputElement.focus();   // 入力欄にフォーカスを戻す
    filterRows();
});

// 逐次検索スクリプト
const rows = [...document.querySelectorAll('.song-row')];
const searchBox = document.getElementById('searchBox');
const searchCount = document.getElementById('searchCount');

let timer;

searchBox.addEventListener('input', () => {

    clearTimeout(timer);

    timer = setTimeout(filterRows, 120);

});


function filterRows() {

    const words = searchBox.value
        .normalize('NFKC')
        .toLowerCase()
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    const checkedInputs = document.querySelectorAll('input[name="tag-chkd"]:checked');
    const selectedTags = Array.from(checkedInputs).map(input => input.value);

    const checkedGenreInputs = document.querySelectorAll('input[name="genre-chkd"]:checked');
    const selectedGenres = Array.from(checkedGenreInputs).map(input => input.value);
    const selectedGenresStr = selectedGenres.join("");


    // 検索文字が空 タグ未選択 ジャンル全選択なら全件表示
    if (words.length === 0 && selectedTags.length === 0 && selectedGenresStr.length === 7) {
        rows.forEach(row => row.classList.remove('song-hidden'));
        searchCount.textContent = `${rows.length} / ${rows.length} 件`;

        return;
    }

    let visible = 0;

    let tagMatch = true;
    let match = true;

    rows.forEach(row => {
        if (words.length === 0) {
            match = true;
        } else {
            const target = row.dataset.search;
            match = words.every(word => target.includes(word));
        }

        if (selectedTags.length === 0) {
            tagMatch = true;
        } else {

            const tags = row.dataset.tags;
            const tagsArray = tags.split(' ');
            tagMatch = selectedTags.some(word => tagsArray.includes(word));
        }

        const genre = row.dataset.genre;
        const genreMatch = selectedGenresStr.includes(genre);

        if (match && tagMatch && genreMatch) {
            row.classList.remove('song-hidden');
            visible++;
        } else {
            row.classList.add('song-hidden');
        }


    });

    searchCount.textContent =
        `${visible} / ${rows.length} 件`;
}


// タグ処理 


// タイマーを制御して実行を遅らせる関数
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        // 連続で呼ばれたら、前のタイマーをキャンセルしてリセットする
        clearTimeout(timeoutId);
        // 新しくタイマーをセットする
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

// n秒待つ関数を作成
const debouncedProcess = debounce(filterRows, 300);

// タグのすべてのチェックボックスにイベントを設定
document.querySelectorAll('input[name="tag-chkd"]').forEach(checkbox => {
    checkbox.addEventListener('change', debouncedProcess);
});


// ジャンルのすべてのチェックボックスにイベントを設定

document.querySelectorAll('input[name="genre-chkd"]').forEach(checkbox => {
    checkbox.addEventListener('change', debouncedProcess);
});
