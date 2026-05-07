window.PaginationManager = function ({
    containerId,
    loadFunction,
    maxVisible = 5
}) {

    return {
        currentPage: 1,
        searchKeyword: '',

        render(lastPage, currPage) {

            const paginationDiv = document.getElementById(containerId);

            paginationDiv.innerHTML = '';

            let start = Math.max(1, currPage - Math.floor(maxVisible / 2));
            let end = start + maxVisible - 1;

            if (end > lastPage) {
                end = lastPage;
                start = Math.max(1, end - maxVisible + 1);
            }

            // <<
            const firstBtn = document.createElement('button');
            firstBtn.innerHTML = '<<';
            firstBtn.className = 'px-3 py-1 border rounded bg-white text-gray-600';
            firstBtn.disabled = currPage === 1;
            firstBtn.onclick = () => loadFunction(1, this.searchKeyword);
            paginationDiv.appendChild(firstBtn);

            // <
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = '<';
            prevBtn.className = 'px-3 py-1 border rounded bg-white text-gray-600';
            prevBtn.disabled = currPage === 1;
            prevBtn.onclick = () => loadFunction(currPage - 1, this.searchKeyword);
            paginationDiv.appendChild(prevBtn);

            // ...
            if (start > 1) {
                const dots = document.createElement('span');
                dots.innerText = '...';
                dots.className = 'px-2';
                paginationDiv.appendChild(dots);
            }

            // page numbers
            for (let i = start; i <= end; i++) {
                const btn = document.createElement('button');

                btn.innerText = i;

                btn.className = `px-3 py-1 border rounded ${i === currPage
                        ? 'bg-green-400 text-white font-bold'
                        : 'bg-white text-gray-600'
                    }`;

                btn.onclick = () => loadFunction(i, this.searchKeyword);

                paginationDiv.appendChild(btn);
            }

            // ...
            if (end < lastPage) {
                const dots = document.createElement('span');
                dots.innerText = '...';
                dots.className = 'px-2';
                paginationDiv.appendChild(dots);
            }

            // >
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = '>';
            nextBtn.className = 'px-3 py-1 border rounded bg-white text-gray-600';
            nextBtn.disabled = currPage === lastPage;
            nextBtn.onclick = () => loadFunction(currPage + 1, this.searchKeyword);
            paginationDiv.appendChild(nextBtn);

            // >>
            const lastBtn = document.createElement('button');
            lastBtn.innerHTML = '>>';
            lastBtn.className = 'px-3 py-1 border rounded bg-white text-gray-600';
            lastBtn.disabled = currPage === lastPage;
            lastBtn.onclick = () => loadFunction(lastPage, this.searchKeyword);
            paginationDiv.appendChild(lastBtn);
        }
    };
};