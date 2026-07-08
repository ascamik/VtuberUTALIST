const container = document.getElementById("song-list"); // song-rowの親

const srows = [...container.querySelectorAll(".song-row")];

let sortColumn = -1;
let sortMode = 0;

document.querySelectorAll(".sortable").forEach(header => {

    header.addEventListener("click", () => {

        const col = Number(header.dataset.col);

        if (sortColumn !== col) {
            sortColumn = col;
            sortMode = 1;
        } else {
            sortMode = (sortMode + 1) % 3;
        }

        sortRows();
        updateMarks();

    });

});

function updateMarks() {

    document.querySelectorAll(".sortable").forEach(header => {

        const mark = header.querySelector(".sort-mark");

        if (Number(header.dataset.col) !== sortColumn || sortMode === 0) {

            mark.textContent = "";

        } else {

            mark.textContent =
                sortMode === 1 ? "▲" : "▼";

        }

    });

}

function getCellValue(row, col) {
    const cell = row.children[col];

    return (cell.dataset.sort ?? cell.textContent)
        .trim();
}

function compareValues(a, b, type) {

    if (a === "" && b === "") return 0;
    if (a === "") return 1;
    if (b === "") return -1;

    switch (type) {

        case "number":
            return Number(a) - Number(b);

        case "date":
            return new Date(a) - new Date(b);

        default:
            return a.localeCompare(
                b,
                "ja",
                {
                    numeric: true,
                    sensitivity: "base"
                }
            );
    }
}

function sortRows() {

    if (sortMode === 0) {

        // 元の並びに戻す
        srows.sort((a, b) =>
            Number(a.dataset.index) - Number(b.dataset.index)
        );

    } else {

        const header = document.querySelector(
            `.sortable[data-col="${sortColumn}"]`
        );

        const type = header.dataset.type ?? "text";

        srows.sort((a, b) => {

            const av = getCellValue(a, sortColumn);
            const bv = getCellValue(b, sortColumn);

            const result = compareValues(av, bv, type);

            return sortMode === 1 ? result : -result;
        });

    }

    // DOMを並べ替える
    srows.forEach(row => container.appendChild(row));
}