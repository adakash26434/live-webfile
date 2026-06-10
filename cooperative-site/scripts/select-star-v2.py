#!/usr/bin/env python3
"""
SELECT * → explicit column list replacement (v2)
Handles remaining ~71 occurrences across admin/ and member/ PHP files.
Skips: dynamic tables, JOINs, backup/export, already-explicit queries.
"""
from __future__ import annotations
import re
from pathlib import Path

ROOT = Path("/app/cooperative-site")

# ──────────────────────────────────────────────────────────────────────────────
# COLUMN MAP — table → explicit column list
# ──────────────────────────────────────────────────────────────────────────────
COLS: dict[str, list[str]] = {
    "members": [
        "id", "name", "email", "phone", "sadasyata_number", "password_hash",
        "google_id", "facebook_id", "avatar_url", "member_card_no", "address",
        "dob", "gender", "approval_status", "approved_at", "approved_by",
        "rejection_reason", "id_card_generated", "id_card_generated_at",
        "is_verified", "is_active", "created_at", "last_login",
    ],
    "kyc_applications": [
        "id", "member_id", "full_name", "full_name_en", "dob_bs", "dob_ad",
        "gender", "marital_status", "nationality", "mobile", "email",
        "permanent_address", "temporary_address", "citizenship_no",
        "citizenship_issued_date", "citizenship_issued_place", "father_name",
        "mother_name", "grandfather_name", "spouse_name", "occupation",
        "organization_name", "monthly_income", "account_type", "branch",
        "photo", "citizenship_front", "citizenship_back", "signature",
        "status", "remarks", "created_at", "updated_at",
    ],
    "loan_applications": [
        "id", "full_name", "member_id", "mobile", "email", "address",
        "citizenship_no", "loan_type", "loan_amount", "loan_purpose",
        "loan_tenure", "repayment_method", "occupation", "organization_name",
        "monthly_income", "other_income", "collateral_type",
        "collateral_description", "collateral_value", "guarantor_name",
        "guarantor_relation", "guarantor_phone", "guarantor_address",
        "branch", "documents", "status", "remarks", "created_at", "updated_at",
    ],
    "account_applications": [
        "id", "account_type", "full_name", "full_name_en", "dob_bs", "dob_ad",
        "gender", "marital_status", "mobile", "email", "permanent_address",
        "temporary_address", "citizenship_no", "citizenship_issued_date",
        "citizenship_issued_place", "father_name", "mother_name", "occupation",
        "monthly_income", "initial_deposit", "nominee_name", "nominee_relation",
        "nominee_phone", "branch", "photo", "citizenship_front",
        "citizenship_back", "signature", "status", "remarks",
        "created_at", "updated_at",
    ],
    "digital_service_requests": [
        "id", "tracking_id", "requester_name", "member_id", "phone", "email",
        "service_type", "service_type_np", "account_number", "statement_from",
        "statement_to", "biller_name", "bill_reference", "recharge_number",
        "recharge_amount", "service_amount", "request_details", "attachment",
        "preferred_contact", "status", "admin_remarks", "admin_attachment",
        "reviewed_by", "reviewed_at", "created_at", "updated_at",
    ],
    "auction_notices": [
        "id", "tracking_number", "title", "title_en", "description",
        "description_en", "property_type", "location", "google_map_link",
        "google_map_embed", "area_bigha", "area_ropani", "area_aana",
        "area_paisa", "area", "minimum_price", "auction_date", "auction_time",
        "contact_person", "contact_phone", "image", "images", "document",
        "status", "is_active", "created_at", "updated_at",
    ],
    "member_notifications": [
        "id", "member_id", "title", "message", "type", "link",
        "is_read", "created_at",
    ],
    "election_positions": [
        "id", "cycle_id", "title_np", "title_en", "seats",
        "max_votes_per_voter", "committee_type_id", "display_order",
        "is_active", "created_at",
    ],
    "election_candidates": [
        "id", "cycle_id", "position_id", "name", "name_en", "photo",
        "bio_np", "bio_en", "phone", "email", "address", "symbol_no",
        "display_order", "is_active", "created_at",
    ],
    "election_posts": [
        "id", "designation_id", "title_np", "title_en", "committee_type_id",
        "default_seats", "default_max_votes", "display_order",
        "is_active", "created_at",
    ],
    "designations": [
        "id", "title_np", "title_en", "category", "display_order",
        "is_active", "created_at", "updated_at",
    ],
    "partner_facilities": [
        "id", "partner_name", "location", "facility_type",
        "discount_percent", "description", "is_active",
        "display_order", "created_at", "updated_at",
    ],
    "site_license_renewal_notices": [
        "id", "status", "gateway", "txn_reference", "amount_reported",
        "note", "submitted_by_admin_id", "submitted_by_username", "created_at",
    ],
    "hrm_employees": [
        "id", "employee_code", "admin_user_id", "full_name_np", "full_name_en",
        "photo", "gender", "dob_bs", "dob_ad", "blood_group", "marital_status",
        "nationality", "religion", "ethnicity", "citizenship_no",
        "citizenship_issued_district", "citizenship_issued_date_bs", "pan_no",
        "nid_no", "passport_no", "driving_license_no", "mobile", "alt_mobile",
        "email", "perm_province", "perm_district", "perm_municipality",
        "perm_ward", "perm_tole", "temp_province", "temp_district",
        "temp_municipality", "temp_ward", "temp_tole", "designation",
        "department_id", "branch_id", "employment_type", "grade", "level",
        "reporting_to", "join_date_bs", "join_date_ad", "confirm_date_bs",
        "confirm_date_ad", "probation_months", "status", "exit_date_ad",
        "exit_reason", "remarks", "created_by", "updated_by",
        "created_at", "updated_at",
    ],
    "member_welfare_claims": [
        "id", "tracking_id", "member_name", "member_id", "phone", "email",
        "address", "claim_type", "claim_amount", "description",
        "claim_date_bs", "claim_date_ad", "status", "approved_amount",
        "admin_remarks", "attachment_path", "created_at", "updated_at",
    ],
    "member_transactions": [
        "id", "member_id", "transaction_type", "amount", "description",
        "remarks", "reference_no", "created_at",
    ],
}

# Skip these files — dynamic tables or intentional SELECT *
SKIP_FILES = {
    "admin/backup-restore.php",  # intentional: exports all columns
    "member/tracker.php",        # dynamic table, can't enumerate columns
}

# Skip these patterns — complex JOIN queries or $viewTbl
SKIP_PATTERNS = [
    r"SELECT\s+\*\s+FROM\s+\$",           # dynamic table name
    r"SELECT\s+\*\s+FROM\s+\w+\s+[a-zA-Z0-9_]+\s+(JOIN|INNER|LEFT|RIGHT|FULL)",  # JOINs
    r"JOIN\s+",                            # any JOIN in same statement
]

def col_list(table: str, alias: str = "") -> str:
    """Return comma-joined explicit column list, optionally prefixed with alias."""
    cols = COLS[table]
    if alias:
        return ", ".join(f"{alias}.{c}" for c in cols)
    return ", ".join(cols)

def should_skip_line(line: str) -> bool:
    for pat in SKIP_PATTERNS:
        if re.search(pat, line, re.IGNORECASE):
            return True
    return False

def process_file(path: Path) -> tuple[int, list[str]]:
    rel = str(path.relative_to(ROOT))
    if rel in SKIP_FILES:
        return 0, []

    original = path.read_text(encoding="utf-8")
    lines = original.split("\n")
    changed = 0
    changes = []

    new_lines = []
    i = 0
    while i < len(lines):
        line = lines[i]
        # Check for SELECT * pattern (case-insensitive)
        if re.search(r'SELECT\s+\*\s+FROM\s+(\w+)', line, re.IGNORECASE):
            m = re.search(r'SELECT\s+\*\s+FROM\s+(\w+)', line, re.IGNORECASE)
            table = m.group(1).lower()

            if table in COLS and not should_skip_line(line):
                replacement = col_list(table)
                new_line = re.sub(
                    r'SELECT\s+\*\s+FROM\s+' + re.escape(m.group(1)),
                    f'SELECT {replacement} FROM {m.group(1)}',
                    line,
                    flags=re.IGNORECASE
                )
                if new_line != line:
                    new_lines.append(new_line)
                    changed += 1
                    changes.append(f"  Line {i+1}: {table}")
                    i += 1
                    continue

        new_lines.append(line)
        i += 1

    if changed:
        path.write_text("\n".join(new_lines), encoding="utf-8")

    return changed, changes


def main():
    targets = list(ROOT.glob("admin/**/*.php")) + list(ROOT.glob("member/**/*.php")) + list(ROOT.glob("includes/**/*.php"))
    total = 0
    for p in sorted(targets):
        n, changes = process_file(p)
        if n:
            print(f"\n{'='*60}")
            print(f"FILE: {p.relative_to(ROOT)} ({n} replacements)")
            for c in changes:
                print(c)
            total += n
    print(f"\n{'='*60}")
    print(f"TOTAL: {total} SELECT * replacements made")


if __name__ == "__main__":
    main()
