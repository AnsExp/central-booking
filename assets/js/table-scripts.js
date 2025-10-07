function sort_table(table_id,column) {
    const table = document.getElementById(table_id);
    const rows = Array.from(table.querySelectorAll("tbody tr"));

    rows.sort((a, b) => {
        const a_text = a.children[column].textContent.trim();
        const b_text = b.children[column].textContent.trim();

        return a_text.localeCompare(b_text);
    });

    rows.forEach(row => table.querySelector("tbody").appendChild(row));
}
