import sys
import json
import io
import os

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

from pulp import *

def solve_optimization(data):
    try:

        target = data.get("target", {})
        dishes = data.get("dishes", [])
        forbidden_keywords = data.get("forbidden_keywords", [])

        if len(dishes) == 0:
            return {
                "status": "error",
                "message": "Không có dữ liệu món ăn."
            }

        budget_target = float(target.get("budget", 999999))

        calories_target = float(target.get("calories", 0))
        protein_target = float(target.get("protein", 0))
        fat_target = float(target.get("fat", 0))
        fiber_target = float(target.get("fiber", 0))

        valid_dishes = []

        for d in dishes:

            tags = " ".join([
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
                "message": "Không còn món ăn nào sau khi lọc dị ứng."
            }

        prob = LpProblem(
            "Catering_Menu_Optimization",
            LpMinimize
        )

        dish_vars = {}

        for d in valid_dishes:
            dish_vars[d["id"]] = LpVariable(
                f"dish_{d['id']}",
                lowBound=0,
                upBound=2,
                cat="Integer"
            )

        # Hàm mục tiêu: tối thiểu hóa chi phí
        prob += lpSum(
            dish_vars[d["id"]] *
            float(d.get("cost_per_serving", 0))
            for d in valid_dishes
        )

        # Ít nhất 2 món
        prob += lpSum(
            dish_vars[d["id"]]
            for d in valid_dishes
        ) >= 2

        # Calories
        if calories_target > 0:
            prob += lpSum(
                dish_vars[d["id"]] *
                float(d.get("calories_per_serving", 0))
                for d in valid_dishes
            ) <= calories_target

        # Protein
        if protein_target > 0:
            prob += lpSum(
                dish_vars[d["id"]] *
                float(d.get("protein_per_serving", 0))
                for d in valid_dishes
            ) >= protein_target

        # Fat
        if fat_target > 0:
            prob += lpSum(
                dish_vars[d["id"]] *
                float(d.get("fat_per_serving", 0))
                for d in valid_dishes
            ) >= fat_target

        # Fiber
        if fiber_target > 0:
            prob += lpSum(
                dish_vars[d["id"]] *
                float(d.get("glucid_per_serving", 0))
                for d in valid_dishes
            ) >= fiber_target

        # Ngân sách
        if budget_target > 0:
            prob += lpSum(
                dish_vars[d["id"]] *
                float(d.get("cost_per_serving", 0))
                for d in valid_dishes
            ) <= budget_target

        cbc_path = r"C:\Users\ngocl\AppData\Local\Programs\Python\Python313\Lib\site-packages\pulp\solverdir\cbc\win\i64\cbc.exe"

        if os.path.exists(cbc_path):
            solver = COIN_CMD(
                path=cbc_path,
                msg=False
            )
        else:
            solver = PULP_CBC_CMD(msg=False)

        prob.solve(solver)

        status = LpStatus[prob.status]

        if status != "Optimal":

            return {
                "status": "error",
                "message": f"Không tìm được thực đơn phù hợp."
            }

        selected_dishes = []

        for d in valid_dishes:

            val = value(dish_vars[d["id"]])

            if val is None:
                continue

            qty = int(round(val))

            if qty > 0:
                selected_dishes.append({
                    "id": d["id"],
                    "quantity": qty
                })

        if len(selected_dishes) == 0:
            return {
                "status": "error",
                "message": "Không có món nào được chọn."
            }

        return {
            "status": "success",
            "dishes": selected_dishes
        }

    except Exception as e:

        return {
            "status": "error",
            "message": str(e)
        }


if __name__ == "__main__":

    if len(sys.argv) < 2:

        print(json.dumps({
            "status": "error",
            "message": "Thiếu file dữ liệu."
        }, ensure_ascii=False))

        sys.exit()

    try:

        with open(sys.argv[1], "r", encoding="utf-8") as f:
            payload = json.load(f)

        result = solve_optimization(payload)

        print(
            json.dumps(
                result,
                ensure_ascii=False
            )
        )

    except Exception as e:

        print(json.dumps({
            "status": "error",
            "message": str(e)
        }, ensure_ascii=False))