CPANEL UPDATE PACKAGE - IMPORTANT

यो ZIP code update का लागि हो। Live data सुरक्षित राख्न:

1. cPanel मा extract गर्नु अघि full backup लिनुहोस्:
   - public_html files backup
   - MySQL database export

2. यो package मा live user-upload data folders intentionally राखिएको छैन:
   - assets/uploads/
   - cache/
   - logs/
   - memory/
   - test_reports/
   - .git / .emergent

3. Direct extract गर्दा existing live uploads/database data delete हुँदैन।
   ZIP extract ले सामान्यतया existing extra files delete गर्दैन, तर overwrite हुने files code files मात्र हुन्।

4. Database schema changes needed हुन सक्छन्, especially institutional_profile table का new columns:
   - other_fund
   - bank_cash_balance
   - fixed_assets
   - total_loan_members
   Admin institutional-profile page मा auto ALTER logic राखिएको छ। Live admin open/save गर्दा columns auto add हुने गरी code छ।

5. Live site मा extract पछि verify गर्नुहोस्:
   - Mobile menu hamburger opens drawer
   - Notice popup desktop/mobile
   - Online KYC province dropdown duplicate छैन
   - Institutional profile admin form save
   - Public institutional profile month cards

6. यदि live config/database credentials अलग file मा छन् भने overwrite नगर्नुहोस्। यो package मा includes/database.php छैन।