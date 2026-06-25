//ワンクリックでセットリストへ追加する
// 
// 対象のボタンをすべて取得
const posts = document.querySelectorAll('.appendSLBtn');

posts.forEach(button => {
    button.addEventListener('click', (event) => {
        // data属性から値を取得（自動で文字列になります）
        const songid = event.currentTarget.dataset.songid;
        const arrng = event.currentTarget.dataset.arrng;

        // 既存の処理関数を呼び出し
        sendPost(songid, arrng);
    });
});

const ecodeElement = document.getElementById('evwcodeMemory');

function sendPost(songid, arrng) {
    const editorElement = document.getElementById('editorpanel');
    const currentModeFlag = editorElement.dataset.mode;
    const currentEcCode = ecodeElement.dataset.ec;
    // 送信用の隠しフォームを動的に生成
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'iedprcsinsertdraft.php'; // 送信先URL

    // songid
    const inputWord = document.createElement('input');
    inputWord.type = 'hidden';
    inputWord.name = 'songid';
    inputWord.value = songid;
    form.appendChild(inputWord);

    // arrng
    const inputTarget = document.createElement('input');
    inputTarget.type = 'hidden';
    inputTarget.name = 'arrng';
    inputTarget.value = arrng;
    form.appendChild(inputTarget);

    // mode
    const inputMode = document.createElement('input');
    inputMode.type = 'hidden';
    inputMode.name = 'mode';
    inputMode.value = currentModeFlag;
    form.appendChild(inputMode);

    // evwcode
    const inputCode = document.createElement('input');
    inputCode.type = 'hidden';
    inputCode.name = 'evwcode';
    inputCode.value = currentEcCode;
    form.appendChild(inputCode);


    // フォームを本体に追加して送信
    document.body.appendChild(form);
    form.submit();
}
//ここまで歌をセットリストに挿入スクリプト
// Script for inserting songs into setlist up to this point
//

//   ---   ---    ---    ---    ---    ---
//ここからセットリストの編集
const modal = document.getElementById('modalOverlay');
/*  const modal = document.getElementById('editModal');
*/

const field1 = document.getElementById('field1');
const field2 = document.getElementById('field2');
const field3 = document.getElementById('field3');
const field4 = document.getElementById('field4');

const editorElement = document.getElementById('editorpanel');
const currentModeFlag = editorElement.dataset.mode;

let currentId = null;
let currentRow = null;
let currentSeqnum = null;


/*
 * 共通POST
 */
async function sendAction(formData) {



    try {

        const response = await fetch('iedprcsinsertdraft.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('HTTP Error');
        }

        const result = await response.json();

        if (result.success) {

            location.reload();


        } else {

            alert(result.message || '更新失敗');

        }

    } catch (err) {

        console.error(err);
        alert('通信エラー');

    }
}


/*
 * 行クリック 編集
 */
document.querySelectorAll('.row').forEach(row => {

    row.addEventListener('click', () => {

        currentRow = row;
        currentId = row.dataset.id;

        const cells = row.querySelectorAll('.cell');

        field1.value = cells[0].textContent.trim();
        field2.value = cells[1].textContent.trim();
        currentSeqnum = cells[2].textContent.trim();
        field3.value = cells[3].textContent.trim();
        field4.value = cells[8].textContent.trim();
        document.getElementById("editing").innerHTML = cells[2].textContent.trim() + ":" + cells[4].textContent.trim();
        modal.style.display = 'flex';
    });

});

/*
 * 閉じる
 */
document.getElementById('closeBtn').addEventListener('click', () => {
    modal.style.display = 'none';
});

/*
 * 保存
 */
document.getElementById('saveBtn').addEventListener('click', async () => {

    const formData = new FormData();
    formData.append('evwcode', currentId);
    formData.append('seqnum', currentSeqnum);
    formData.append('songid', field1.value);
    formData.append('arrng', field2.value);
    formData.append('time', field3.value);
    formData.append('memo', field4.value);

    formData.append('mode', currentModeFlag);

    sendAction(formData);



});
/*
 * 削除
 */
document.querySelectorAll('.delete-row').forEach(btn => {

    btn.addEventListener('click', (e) => {

        e.stopPropagation();

        if (!confirm('削除しますか？')) {
            return;
        }

        const row = btn.closest('.row');

        const formData = new FormData();

        formData.append('action', 'delete');
        formData.append('evwcode', row.dataset.id);
        formData.append('seqnum', row.dataset.num);
        formData.append('mode', currentModeFlag);


        sendAction(formData);

    });

});

/*
 * 上へ移動
 */
document.querySelectorAll('.move-up').forEach(btn => {

    btn.addEventListener('click', (e) => {

        e.stopPropagation();

        const row = btn.closest('.row');

        const formData = new FormData();

        formData.append('action', 'move_up');
        formData.append('evwcode', row.dataset.id);
        formData.append('seqnum', row.dataset.num);
        formData.append('mode', currentModeFlag);
        sendAction(formData);

    });

});

/*
 * 下へ移動
 */
document.querySelectorAll('.move-down').forEach(btn => {

    btn.addEventListener('click', (e) => {

        e.stopPropagation();

        const row = btn.closest('.row');

        const formData = new FormData();

        formData.append('action', 'move_down');
        formData.append('evwcode', row.dataset.id);
        formData.append('seqnum', row.dataset.num);
        formData.append('mode', currentModeFlag);
        sendAction(formData);

    });

});
//   ---   ---    ---    ---    ---    ---
//Event管理用
//新規追加
const dialog = document.getElementById('eventDialog');
let newEventMode = 0;

document
    .getElementById('newEventBtn')
    .addEventListener('click', () => {

        evwcode.value = '';
        evdate.value = '';
        evtitle.value = '';
        evurl.value = '';
        evmedia.value = '1';
        evtype.value = '1';
        evdesc.value = '';

        newEventMode = 1;//create new mode

        deleteEvBtn.style.display = 'none';

        const labelElement = document.getElementById('label-evwcode');

        labelElement.textContent = 'コード（空欄のままで自動決定します［推奨］⚠️すでに使われているコードは設定できません）';

        const inputElement = document.getElementById('evwcode');

        inputElement.readOnly = false;

        dialog.showModal();
        // console.log('modalopen');
    });

//編集（モーダルオープン）
document
    .querySelectorAll('.event-row')
    .forEach(row => {

        row.addEventListener('click', async () => {

            const evwcode =
                row.dataset.evwcode;

            try {
                // const response =
                //     await fetch(
                //         'iedapi_event-get.php?evwcode=' +
                //         encodeURIComponent(evwcode)
                //     );

                const response = await fetch('iedapi_event-get.php?evwcode=' +
                    encodeURIComponent(evwcode));

                if (!response.ok) {
                    throw new Error(
                        `HTTP ${response.status}`
                    );
                }

                const result =
                    await response.json();

                if (!result.success) {
                    alert(result.message);
                    return;
                }

                // 正常処理
                const labelElement = document.getElementById('label-evwcode');

                labelElement.textContent = 'コード';

                const inputElement = document.getElementById('evwcode');

                inputElement.readOnly = true;
                const data = result.data;

                newEventMode = 0; //update mode

                document.getElementById('evwcode').value =
                    data.evwcode;

                document.getElementById('evdate').value =
                    data.evdate;

                document.getElementById('evtitle').value =
                    data.evtitle;

                document.getElementById('evurl').value =
                    data.evurl;

                document.getElementById('evmedia').value =
                    data.evmedia;

                document.getElementById('evtype').value =
                    data.evtype;

                document.getElementById('evdesc').value =
                    data.evdesc;

                deleteEvBtn.style.display = '';

                dialog.showModal();
            }
            catch (e) {

                alert(
                    'データ取得に失敗しました'
                );

                console.error(e);

            }
        });

    });

//保存
saveEvBtn.addEventListener('click', async () => {

    const formData = new FormData();

    formData.append('evwcode', evwcode.value);
    formData.append('evdate', evdate.value);
    formData.append('evtitle', evtitle.value);
    formData.append('evurl', evurl.value);
    formData.append('evmedia', evmedia.value);
    formData.append('evtype', evtype.value);
    formData.append('evdesc', evdesc.value);

    if (newEventMode === 1) {

        formData.append('mode', '1');

    }

    const response =
        await fetch(
            'iedapi_event-save.php',
            {
                method: 'POST',
                body: formData
            }
        );

    const result =
        await response.json();

    if (!result.success) {
        alert(result.message);
        return;
    }

    location.reload();
});

//削除
deleteEvBtn.addEventListener('click', async () => {

    if (!confirm('削除しますか？')) {
        return;
    }

    const formData = new FormData();

    formData.append(
        'evwcode',
        evwcode.value
    );

    const response =
        await fetch(
            'iedapi_event-delete.php',
            {
                method: 'POST',
                body: formData
            }
        );

    const result =
        await response.json();

    if (!result.success) {
        alert(result.message);
        return;
    }

    location.reload();
});
//閉じる
closeEvBtn.addEventListener('click', () => {
    dialog.close();
});

//Event管理用ここまで


//(新)Song修正／削除Script
// 行クリック 編集開始
const dialogSong = document.getElementById('songDialog');

document
    .querySelectorAll('.song-row')
    .forEach(row => {

        // row.addEventListener('click', () => {
        row.addEventListener('click', async (e) => {

            if (e.target.classList.contains('appendSLBtn')) {
                return; // ボタンのある右端の要素のみモーダル開かないように
            }

            const songid = row.dataset.songid;
            const arrng = row.dataset.arrng;

            try {
                const response = await fetch('iedapi_song-get.php?songid=' +
                    encodeURIComponent(songid) + '&arrng=' + encodeURIComponent(arrng));

                if (!response.ok) {
                    throw new Error(
                        `HTTP ${response.status}`
                    );
                }

                const result =
                    await response.json();

                if (!result.success) {
                    alert(result.message);
                    return;
                }
                // 正常処理
                const data = result.data;
                document.getElementById('songid').value =
                    data.songid;
                document.getElementById('arrng').value =
                    data.arrng;
                document.getElementById('sname').value =
                    data.sname;
                document.getElementById('yomi').value =
                    data.yomi;
                document.getElementById('artist').value =
                    data.artist;
                document.getElementById('tieup').value =
                    data.tieup;
                document.getElementById('vocap').value =
                    data.vocap;
                document.getElementById('genre').value =
                    data.genre;
                document.getElementById('relsd').value =
                    data.relsd;


                dialogSong.showModal();

            } catch (e) {

                alert(
                    'データ取得に失敗しました'
                );

                console.error(e);

            }





        });
    });

//保存
saveSongBtn.addEventListener('click', async () => {

    const formData = new FormData();

    formData.append('songid', songid.value);
    formData.append('arrng', arrng.value);
    formData.append('sname', sname.value);
    formData.append('yomi', yomi.value);
    formData.append('artist', artist.value);
    formData.append('tieup', tieup.value);
    formData.append('vocap', vocap.value);
    formData.append('genre', genre.value);
    formData.append('relsd', relsd.value);

    const response =
        await fetch(
            'iedapi_song-update.php',
            {
                method: 'POST',
                body: formData
            }
        );

    const result =
        await response.json();

    if (!result.success) {
        alert(result.message);
        return;
    }

    location.reload();
});















//削除
deleteSongBtn.addEventListener('click', async () => {

    if (!confirm('削除しますか？')) {
        return;
    }

    const formData = new FormData();

    formData.append('songid', songid.value);
    formData.append('arrng', arrng.value);

    const response =
        await fetch(
            'iedapi_song-delete.php',
            {
                method: 'POST',
                body: formData
            }
        );

    const result =
        await response.json();

    if (!result.success) {
        alert(result.message);
        return;
    }

    location.reload();
});







//閉じる
closeSongBtn.addEventListener('click', () => {
    dialogSong.close();
});