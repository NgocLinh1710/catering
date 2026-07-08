<div id="aiChatBubble" onclick="toggleChatWindow()" class="fixed bottom-6 right-6 z-50 cursor-pointer
           transition-all duration-300
           hover:scale-110 hover:rotate-6
           active:scale-90">
    <img src="{{ asset('images/chatbot-mushroom.png') }}" alt="AI Chatbot" draggable="false"
        class="mushroom-chatbot w-16 h-16 object-contain">
</div>

<div id="aiChatWindow" class="hidden fixed bottom-24 right-6 w-96 h-[550px]
           bg-white rounded-2xl shadow-2xl border border-gray-200
           z-50 flex flex-col overflow-hidden transition-all">

    <div class="bg-gradient-to-r from-[#6ee7a0] to-[#86efac] text-gray-900
                p-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <i class="fas fa-leaf text-xl"></i>
            <span class="font-bold text-sm tracking-wide">
                Trợ lý Thực đơn & Dinh dưỡng AI
            </span>
        </div>
        <button onclick="toggleChatWindow()" class="hover:text-gray-200 focus:outline-none transition">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <div class="bg-gray-50 p-2 border-b flex space-x-1 shadow-inner">
        <button onclick="changeAiMode('nutrition_analysis', this)" class="ai-mode-btn flex-1 py-1.5 text-xs font-semibold rounded-md
                   bg-[#86efac] text-gray-900 transition shadow-sm">
            <i class="fas fa-chart-pie block text-center mb-0.5"></i>
            Kiểm duyệt thực đơn
        </button>

        <button onclick="changeAiMode('dish_lookup', this)" class="ai-mode-btn flex-1 py-1.5 text-xs font-semibold rounded-md
                   bg-transparent text-gray-500 hover:bg-gray-200 transition">
            <i class="fas fa-search block text-center mb-0.5"></i>
            Tra cứu món & giá
        </button>

        <button onclick="changeAiMode('helpdesk', this)" class="ai-mode-btn flex-1 py-1.5 text-xs font-semibold rounded-md
                   bg-transparent text-gray-500 hover:bg-gray-200 transition">
            <i class="fas fa-question-circle block text-center mb-0.5"></i>
            Hướng dẫn điền form
        </button>
    </div>

    <div id="aiChatMessages" class="flex-1 p-4 overflow-y-auto space-y-4 bg-gray-50 text-sm scroll-smooth">
        <div class="flex items-start space-x-2">
            <div class="bg-white border border-gray-200 text-gray-800
                        p-3 rounded-xl rounded-tl-none shadow-sm
                        max-w-[85%] leading-relaxed">
                Xin chào! Tôi là cố vấn dinh dưỡng của bạn.
                Bạn muốn tôi
                <b>Đánh giá chất lượng thực đơn đang chọn</b>,
                <b>Tra cứu món ăn trong kho</b>
                hay
                <b>Hướng dẫn thao tác màn hình này</b>?
            </div>
        </div>
    </div>

    <div class="p-3 border-t bg-white flex space-x-2 items-center">
        <input type="text" id="aiChatInput" placeholder="Nhập câu hỏi của bạn tại đây..." class="flex-1 border border-gray-300 rounded-xl
                   px-4 py-2.5 text-sm
                   focus:outline-none
                   focus:border-[#86efac]
                   focus:ring-1
                   focus:ring-[#86efac]
                   transition" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); sendChatToAI(); }">

        <button id="sendAiBtn" onclick="sendChatToAI()" class="bg-[#86efac] hover:bg-[#6ee7a0]
           text-gray-900 px-4 py-2.5 rounded-xl
           text-sm transition shadow-sm">
            <i id="sendAiIcon" class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<style>
    @keyframes mushroomBreath {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.06);
        }

        100% {
            transform: scale(1);
        }
    }

    .mushroom-chatbot {
        animation: mushroomBreath 2.8s ease-in-out infinite;
        filter: drop-shadow(0 0 18px rgba(134, 239, 172, .75));
        transition: transform .3s ease, filter .3s ease;
        user-select: none;
        pointer-events: none;
    }

    #aiChatBubble:hover .mushroom-chatbot {
        filter: drop-shadow(0 0 28px rgba(134, 239, 172, 1));
    }
</style>

<script>
    let currentAiMode = 'nutrition_analysis';
    let isSending = false;

    function toggleChatWindow() {
        document.getElementById('aiChatWindow').classList.toggle('hidden');
    }

    function changeAiMode(mode, element) {
        if (isSending) return;
        currentAiMode = mode;
        document.querySelectorAll('.ai-mode-btn').forEach(btn => {
            btn.classList.remove('bg-[#86efac]', 'text-gray-900', 'shadow-sm');
            btn.classList.add('bg-transparent', 'text-gray-500');
        });

        element.classList.remove('bg-transparent', 'text-gray-500');
        element.classList.add('bg-[#86efac]', 'text-gray-900', 'shadow-sm');

        const hints = {
            nutrition_analysis: "Chế độ: <b>Kiểm duyệt thực đơn</b>.<br><i>Hỏi tôi: 'Thực đơn hiện tại đã cân đối Kcal và ngân sách chưa?'</i>",
            dish_lookup: "Chế độ: <b>Tra cứu món ăn</b>.<br><i>Hỏi tôi: 'Món thịt kho tàu có bao nhiêu calo, giá vốn bao nhiêu?'</i>",
            helpdesk: "Chế độ: <b>Hướng dẫn thao tác</b>.<br><i>Hỏi tôi: 'Ô cấu hình nhóm dị ứng dùng để làm gì và điền ra sao?'</i>"
        };

        const msgBox = document.getElementById('aiChatMessages');
        msgBox.innerHTML += `
            <div class="text-center text-xs text-green-800 my-3 font-medium bg-green-100 py-1 rounded-lg">
                ${hints[mode]}
            </div>
        `;
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    function appendMessage(text, isUser = false) {
        const msgBox = document.getElementById('aiChatMessages');
        const msgDiv = document.createElement('div');

        msgDiv.className = isUser ? "flex justify-end" : "flex items-start space-x-2";

        const innerHTML = isUser
            ? `<div class="bg-[#86efac] text-gray-900 p-3 rounded-xl rounded-tr-none shadow-sm max-w-[85%] leading-relaxed">${text}</div>`
            : `<div class="bg-white border border-gray-200 text-gray-800 p-3 rounded-xl rounded-tl-none shadow-sm max-w-[85%] leading-relaxed">${text.replace(/\n/g, '<br>')}</div>`;

        msgDiv.innerHTML = innerHTML;
        msgBox.appendChild(msgDiv);
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    function sendChatToAI() {
        if (isSending) return;
        isSending = true;

        const inputEl = document.getElementById('aiChatInput');
        const sendBtn = document.getElementById("sendAiBtn");
        const sendIcon = document.getElementById("sendAiIcon");
        const message = inputEl.value.trim();

        if (!message) {
            isSending = false;
            return;
        }

        appendMessage(message, true);
        inputEl.value = '';
        inputEl.disabled = true;
        sendBtn.disabled = true;
        sendBtn.classList.add('opacity-50', 'cursor-not-allowed');
        sendIcon.className = "fas fa-spinner fa-spin";

        appendMessage("Trợ lý AI đang phân tích dữ liệu và suy nghĩ...", false);

        const token = localStorage.getItem('access_token') || (typeof window.token !== 'undefined' ? window.token : '');

        // Danh sách món đã chọn
        const finalChosenDishes =
            (typeof chosenDishes !== "undefined") ? chosenDishes : [];

        // Backup dữ liệu nếu UI chưa cập nhật
        let backupCalories = 0;
        let backupCost = 0;
        let backupProtein = 0;
        let backupFat = 0;
        let backupGlucid = 0;

        if (Array.isArray(finalChosenDishes)) {
            finalChosenDishes.forEach(dish => {
                const qty = parseFloat(dish.quantity || dish.servings || 1);

                backupCalories += parseFloat(dish.calories_per_serving || dish.calories || 0) * qty;
                backupCost += parseFloat(dish.cost_per_serving || dish.cost || dish.price || 0) * qty;
                backupProtein += parseFloat(dish.protein_per_serving || dish.protein || 0) * qty;
                backupFat += parseFloat(dish.fat_per_serving || dish.fat || 0) * qty;
                backupGlucid += parseFloat(dish.glucid_per_serving || dish.glucid || dish.carbs || 0) * qty;
            });
        }

        // Đọc dữ liệu từ giao diện
        const calMonitorText =
            document.getElementById("calor-monitor")?.innerText ||
            document.getElementById("target-calories")?.innerText ||
            "";

        const budgetMonitorText =
            document.getElementById("budget-monitor")?.innerText ||
            document.getElementById("target-budget")?.innerText ||
            "";

        const targetCaloriesRaw = calMonitorText.includes("/")
            ? calMonitorText.split("/")[1]
            : calMonitorText;

        const targetBudgetRaw = budgetMonitorText.includes("/")
            ? budgetMonitorText.split("/")[1]
            : budgetMonitorText;

        const finalTargetCalories =
            targetCaloriesRaw.replace(/[^\d.]/g, "") ||
            document.getElementById("target_calories")?.value ||
            "850";

        const finalTargetBudget =
            targetBudgetRaw.replace(/[^\d.]/g, "") ||
            document.getElementById("budget_per_serving")?.value ||
            "35000";

        const uiCal = calMonitorText.includes("/")
            ? calMonitorText.split("/")[0].replace(/[^\d.]/g, "")
            : document.getElementById("current-calories")?.innerText;

        const uiCost = budgetMonitorText.includes("/")
            ? budgetMonitorText.split("/")[0].replace(/[^\d.]/g, "")
            : document.getElementById("current-cost")?.innerText;

        const uiProtein =
            document.getElementById("current-protein")?.innerText?.replace(/[^\d.]/g, "");

        const uiFat =
            document.getElementById("current-fat")?.innerText?.replace(/[^\d.]/g, "");

        const uiGlucid =
            document.getElementById("current-glucid")?.innerText?.replace(/[^\d.]/g, "");

        const currentFormState = {
            target_calories:
                document.getElementById("target-calories-per")?.innerText.replace(/[^\d.]/g, "") || "0",

            target_budget:
                document.getElementById("target-budget-per")?.innerText.replace(/[^\d.]/g, "") || "0",

            current_calories:
                document.getElementById("calc-current-calories")?.innerText.replace(/[^\d.]/g, "") || "0",

            current_cost:
                document.getElementById("calc-current-cost")?.innerText.replace(/[^\d.]/g, "") || "0",

            current_protein:
                document.getElementById("calc-protein")?.innerText.replace(/[^\d.]/g, "") || "0",

            current_fat:
                document.getElementById("calc-fat")?.innerText.replace(/[^\d.]/g, "") || "0",

            current_glucid:
                document.getElementById("calc-fiber")?.innerText.replace(/[^\d.]/g, "") || "0",

            normal_servings:
                document.getElementById("normal-servings")?.value || "0",

            vegetarian_servings:
                document.getElementById("vegetarian-servings")?.value || "0",

            allergy_servings:
                document.getElementById("allergy-servings")?.value || "0",

            chosen_dishes:
                (typeof chosenDishes !== "undefined") ? chosenDishes : []
        };

        console.table(currentFormState);
        fetch('/api/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                mode: currentAiMode,
                message: message,
                current_url: window.location.href,
                form_context: currentFormState,
                ui_description: (typeof getUiDescription === 'function') ? getUiDescription() : "Giao diện hệ thống Catering chung."
            })
        })
            .then(res => res.json())
            .then(res => {
                const msgBox = document.getElementById('aiChatMessages');
                if (msgBox.lastChild) {
                    msgBox.removeChild(msgBox.lastChild);
                }
                if (res.status === 'success') {
                    appendMessage(res.data || res.message, false);
                } else {
                    appendMessage(res.message || "Rất tiếc, AI không phản hồi được lúc này.", false);
                }

                isSending = false;
                inputEl.disabled = false;
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                sendIcon.className = "fas fa-paper-plane";
                inputEl.focus();
            })
            .catch(err => {
                console.error(err);
                const msgBox = document.getElementById('aiChatMessages');
                if (msgBox.lastChild) {
                    msgBox.removeChild(msgBox.lastChild);
                }

                appendMessage("Lỗi kết nối tới Server AI. Vui lòng kiểm tra lại backend API.", false);

                isSending = false;
                inputEl.disabled = false;
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                sendIcon.className = "fas fa-paper-plane";
                inputEl.focus();
            });
    }
</script>