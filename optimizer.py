import sys
import json
import io
import os
import random

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

from pulp import *

def solve_optimization(data):
    try:
        target = data.get("target", {})
        dishes = data.get("dishes", [])
        forbidden_keywords = data.get("forbidden_keywords", [])

        # Kiểm tra nếu đây là suất ăn Chay (Vegetarian)
        is_vegetarian = target.get("is_vegetarian", False) or "chay" in str(target.get("name", "")).lower()
        if is_vegetarian:
            veg_forbidden = ["thịt", "cá", "hải sản", "tôm", "cua", "bò", "gà", "heo", "lợn", "vịt", "ốc", "ếch", "chả lụa", "pate", "lạp xưởng"]
            for kw in veg_forbidden:
                if kw not in forbidden_keywords:
                    forbidden_keywords.append(kw)

        if len(dishes) == 0:
            return {
                "status": "error",
                "message": "Không có dữ liệu món ăn."
            }

        budget_target = float(target.get("budget", 999999))
        calories_target = float(target.get("calories", 0))
        protein_target = float(target.get("protein", 0))
        fat_target = float(target.get("fat", 0))
        glucid_target = float(target.get("glucid", 0))
        fiber_target = float(target.get("fiber", 0))

        valid_dishes = []

        # Lọc các món ăn chứa từ khóa dị ứng / món mặn đối với suất chay
        for d in dishes:
            tags = " ".join([
                str(d.get("name", "")),
                str(d.get("warning_tags", "")),
                " ".join(d.get("dish_tags", []) if isinstance(d.get("dish_tags"), list) else []),
                " ".join(d.get("allergy_tags", []) if isinstance(d.get("allergy_tags"), list) else [])
            ]).lower()

            blocked = False
            for kw in forbidden_keywords:
                if kw and kw.lower() in tags:
                    blocked = True
                    break

            if not blocked:
                valid_dishes.append(d)

        if len(valid_dishes) == 0:
            return {
                "status": "error",
                "message": "Không còn món ăn nào sau khi áp dụng bộ lọc (dị ứng/chay)."
            }

        random.shuffle(valid_dishes)

        main_dishes = [d for d in valid_dishes if d.get("category") == "Món chính"]
        side_dishes = [d for d in valid_dishes if d.get("category") == "Món phụ"]
        soup_dishes = [d for d in valid_dishes if d.get("category") == "Canh"]
        starter_dishes = [d for d in valid_dishes if d.get("category") == "Khai vị"]
        dessert_dishes = [d for d in valid_dishes if d.get("category") == "Tráng miệng"]

        # Triển khai vòng lặp cơ chế dự phòng (Attempt 1: Strict -> Attempt 2: Fallback)
        final_selected_dishes = []
        is_optimal_found = False
        activated_mode = "strict"

        for mode in ["strict", "fallback"]:
            activated_mode = mode
            prob = LpProblem("Catering_Menu_Optimization", LpMinimize)

            # Cấu hình biên độ dinh dưỡng theo từng chế độ chạy
            if mode == "strict":
                calo_low, calo_high = 0.95, 1.05
                macro_low, macro_high = 0.90, 1.10
                fiber_low, fiber_high = 0.80, 1.20
            else:
                # Chế độ dự phòng: Nới rộng đồng loạt 80% - 120% để cứu cánh vô nghiệm
                calo_low, calo_high = 0.80, 1.20
                macro_low, macro_high = 0.80, 1.20
                fiber_low, fiber_high = 0.80, 1.20

            # Định nghĩa lại các biến quyết định cho lượt chạy mới
            dish_vars = {}
            select_vars = {}

            for d in valid_dishes:
                d_id = d["id"]
                category = d.get("category", "")

                if category == "Món chính":
                    max_servings = 4
                elif category == "Món phụ":
                    max_servings = 3
                elif category == "Canh":
                    max_servings = 2
                elif category == "Khai vị":
                    max_servings = 2
                elif category == "Tráng miệng":
                    max_servings = 2
                else:
                    max_servings = 3

                dish_vars[d_id] = LpVariable(f"dish_{d_id}", lowBound=0, upBound=max_servings, cat="Integer")
                select_vars[d_id] = LpVariable(f"select_{d_id}", cat="Binary")

                prob += dish_vars[d_id] >= select_vars[d_id]
                prob += dish_vars[d_id] <= max_servings * select_vars[d_id]

            # Ràng buộc số lượng món
            prob += lpSum(select_vars[d["id"]] for d in valid_dishes) >= 3
            prob += lpSum(select_vars[d["id"]] for d in valid_dishes) <= 5
            
            if main_dishes:
                prob += lpSum(select_vars[d["id"]] for d in main_dishes) >= 1
                prob += lpSum(select_vars[d["id"]] for d in main_dishes) <= 2
            if side_dishes:
                prob += lpSum(select_vars[d["id"]] for d in side_dishes) >= 1
                prob += lpSum(select_vars[d["id"]] for d in side_dishes) <= 2
            if soup_dishes:
                prob += lpSum(select_vars[d["id"]] for d in soup_dishes) >= 1
                prob += lpSum(select_vars[d["id"]] for d in soup_dishes) <= 1
            if starter_dishes:
                prob += lpSum(select_vars[d["id"]] for d in starter_dishes) <= 1
            if dessert_dishes:
                prob += lpSum(select_vars[d["id"]] for d in dessert_dishes) <= 1

            # Hàm mục tiêu
            cost_term = lpSum(dish_vars[d["id"]] * float(d.get("cost_per_serving", 0)) for d in valid_dishes)
            efficiency_term = []
            for d in valid_dishes:
                raw_cost = float(d.get("cost_per_serving", 1))
                cost_k = raw_cost / 1000.0 if raw_cost > 0 else 1.0
                total_nutrients = (
                    float(d.get("calories_per_serving", 0)) +
                    float(d.get("protein_per_serving", 0)) * 4 +  
                    float(d.get("fat_per_serving", 0)) * 9 +
                    float(d.get("glucid_per_serving", 0)) * 4
                )
                efficiency = total_nutrients / cost_k
                category = str(d.get("category", "")).lower()

                if category == "món chính": efficiency *= 1.25
                elif category == "món phụ": efficiency *= 1.10
                elif category == "canh": efficiency *= 1.05
                elif category == "khai vị": efficiency *= 0.95
                elif category == "tráng miệng": efficiency *= 0.90
                    
                efficiency_term.append(dish_vars[d["id"]] * efficiency)
                
            quantity_penalty = lpSum(dish_vars[d["id"]] for d in valid_dishes)
            dish_count_penalty = lpSum(select_vars[d["id"]] for d in valid_dishes)

            prob += (
                cost_term
                - 0.2 * lpSum(efficiency_term)
                + 2.0 * quantity_penalty
                + 5.0 * dish_count_penalty
            )

            # Áp dụng các ràng buộc biên động theo chế độ (strict / fallback)
            if budget_target > 0:
                prob += lpSum(dish_vars[d["id"]] * float(d.get("cost_per_serving", 0)) for d in valid_dishes) <= budget_target

            if calories_target > 0:
                prob += lpSum(dish_vars[d["id"]] * float(d.get("calories_per_serving", 0)) for d in valid_dishes) >= calo_low * calories_target
                prob += lpSum(dish_vars[d["id"]] * float(d.get("calories_per_serving", 0)) for d in valid_dishes) <= calo_high * calories_target

            if protein_target > 0:
                prob += lpSum(dish_vars[d["id"]] * float(d.get("protein_per_serving", 0)) for d in valid_dishes) >= macro_low * protein_target
                prob += lpSum(dish_vars[d["id"]] * float(d.get("protein_per_serving", 0)) for d in valid_dishes) <= macro_high * protein_target

            if fat_target > 0:
                prob += lpSum(dish_vars[d["id"]] * float(d.get("fat_per_serving", 0)) for d in valid_dishes) >= macro_low * fat_target
                prob += lpSum(dish_vars[d["id"]] * float(d.get("fat_per_serving", 0)) for d in valid_dishes) <= macro_high * fat_target

            if glucid_target > 0:
                prob += lpSum(dish_vars[d["id"]] * float(d.get("glucid_per_serving", 0)) for d in valid_dishes) >= macro_low * glucid_target
                prob += lpSum(dish_vars[d["id"]] * float(d.get("glucid_per_serving", 0)) for d in valid_dishes) <= macro_high * glucid_target

            if fiber_target > 0:
                prob += lpSum(dish_vars[d["id"]] * float(d.get("fiber_per_serving", 0)) for d in valid_dishes) >= fiber_low * fiber_target
                prob += lpSum(dish_vars[d["id"]] * float(d.get("fiber_per_serving", 0)) for d in valid_dishes) <= fiber_high * fiber_target

            # Thực thi giải thuật
            solver = PULP_CBC_CMD(msg=False, timeLimit=10)
            prob.solve(solver)
            
            if LpStatus[prob.status] == "Optimal":
                # Thu thập món ăn nếu tìm ra cấu trúc thực đơn tối ưu
                for d in valid_dishes:
                    val = value(dish_vars[d["id"]])
                    if val is None: continue
                    qty = int(round(val))
                    if qty > 0:
                        final_selected_dishes.append({"id": d["id"], "quantity": qty})
                
                if len(final_selected_dishes) > 0:
                    is_optimal_found = True
                    break # Thoát vòng lặp, không cần kích hoạt chế độ nới lỏng nữa

        # Kết thúc vòng lặp kiểm tra kết quả
        if not is_optimal_found:
            return {
                "status": "error",
                "message": "Không tìm được tổ hợp thực đơn nào thỏa mãn kể cả khi đã nới rộng biên độ dinh dưỡng dự phòng (80-120%)."
            }

        return {
            "status": "success",
            "optimization_mode": activated_mode, # Trả thêm flag để frontend/backend biết đang dùng thực đơn chuẩn hay dự phòng
            "dishes": final_selected_dishes
        }

    except Exception as e:
        return {
            "status": "error",
            "message": str(e)
        }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Thiếu file dữ liệu."}, ensure_ascii=False))
        sys.exit()

    try:
        with open(sys.argv[1], "r", encoding="utf-8") as f:
            payload = json.load(f)

        result = solve_optimization(payload)
        print(json.dumps(result, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}, ensure_ascii=False))