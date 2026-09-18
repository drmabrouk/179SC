<?php
/**
 * Template Name: Public Exit Card Request Wizard & Data Update Portal
 * Shortcode: [card]
 */

if (!defined('ABSPATH')) exit;

$school_info = SM_Settings::get_school_info();
$sys_logo = !empty($school_info['school_logo']) ? $school_info['school_logo'] : (!empty($school_info['logo_url']) ? $school_info['logo_url'] : SM_PLUGIN_URL . 'assets/images/logo.png');
$school_name = 'مؤسسة الشعلة للتعليم والتطوير';
$ajax_url = admin_url('admin-ajax.php');
$admin_nonce = wp_create_nonce('sm_admin_action');

$is_admin = is_user_logged_in() && (current_user_can('manage_options') || current_user_can('إدارة_الطلاب'));

$card_settings = get_option('sm_exit_card_settings', array(
    'portal_mode' => 'card_application',
    'verify_method' => 'both',
    'required_fields' => array('guardian_phone', 'dob'),
    'max_requests' => 3,
    'redirect_discipline' => 'yes'
));

$portal_mode = $card_settings['portal_mode'] ?? 'card_application';
$verify_method = $card_settings['verify_method'] ?? 'both';
$required_fields = (array) ($card_settings['required_fields'] ?? array('guardian_phone', 'dob'));
?>

<style>
.eess-card-portal-wrapper {
    max-width: 1100px;
    margin: 20px auto;
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    align-items: flex-start;
    font-family: 'Cairo', sans-serif;
    direction: rtl;
    box-sizing: border-box;
    color: #0f172a;
}
.eess-portal-left-content {
    flex: 1 1 660px;
    min-width: 320px;
    order: 2;
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15,23,42,0.08);
    padding: 24px;
    box-sizing: border-box;
}
.eess-portal-right-sidebar {
    flex: 0 0 280px;
    width: 280px;
    max-width: 100%;
    order: 1;
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15,23,42,0.08);
    padding: 18px;
    box-sizing: border-box;
}
.eess-req-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
.eess-req-fields-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
@media (max-width: 820px) {
    .eess-portal-left-content { order: 2; }
    .eess-portal-right-sidebar { order: 1; width: 100%; flex: 1 1 100%; }
    .eess-req-cards-grid { grid-template-columns: 1fr; }
    .eess-req-fields-grid { grid-template-columns: 1fr; }
}
.sb-nav-btn {
    width: 100%;
    text-align: right;
    background: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 12px 14px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sb-nav-btn.active {
    background: #881337;
    color: #ffffff;
    border: none;
}
.sb-nav-btn.active svg {
    stroke: #ffffff;
}
.eess-btn-action-compact {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    height: 30px;
    padding: 0 8px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 800;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
    box-sizing: border-box;
    text-decoration: none;
    white-space: nowrap;
}
</style>

<!-- In-System Floating Toast Notification Component -->
<div id="eess-toast-notification" style="display: none; position: fixed; bottom: 30px; left: 30px; z-index: 999999; background: #0f172a; color: white; padding: 14px 20px; border-radius: 12px; font-weight: 800; font-size: 13px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); direction: rtl; font-family: 'Cairo', sans-serif; align-items: center; gap: 10px; border-right: 5px solid #16a34a;">
    <span id="eess-toast-icon">✓</span>
    <span id="eess-toast-message">تم التحديث بنجاح.</span>
</div>

<!-- Outer Flexible Layout Wrapper (Left Content + Right Navigation Sidebar) -->
<div class="eess-card-portal-wrapper">

    <!-- LEFT CONTENT PANEL (Dynamic Views) -->
    <div id="eess-portal-content-panel" class="eess-portal-left-content">

        <!-- VIEW 1: MAIN SERVICE WIZARD (DEFAULT) -->
        <div id="pv-view-wizard" style="display: block;">
            <!-- Header & Branding Banner -->
            <div style="text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 20px;">
                <div style="width: 72px; height: 72px; margin: 0 auto 10px auto; background: #ffffff; border-radius: 16px; padding: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center;">
                    <img src="<?php echo esc_url($sys_logo); ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 12px;" alt="Logo">
                </div>
                <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 900; color: #0f172a;"><?php echo esc_html($school_name); ?></h2>
                <div style="font-size: 13px; color: #881337; font-weight: 800;" id="w_portal_subtitle_text">
                    <?php echo ($portal_mode === 'update_only') ? 'بوابة تحديث بيانات الطلاب المعتمدة' : 'بوابة تقديم ومتابعة طلبات بطاقات تصريح الخروج الرقمية'; ?>
                </div>
            </div>

            <!-- Multi-Step Progress Indicator -->
            <div id="w-progress-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; position: relative;">
                <div style="position: absolute; top: 50%; right: 10%; left: 10%; height: 3px; background: #e2e8f0; z-index: 1; transform: translateY(-50%);"></div>
                <div id="w-progress-line" style="position: absolute; top: 50%; right: 10%; width: 0%; height: 3px; background: #881337; z-index: 1; transform: translateY(-50%); transition: width 0.3s ease;"></div>

                <div class="w-step-item active" id="w-step-ind-1" style="position: relative; z-index: 2; text-align: center;">
                    <div class="w-step-num" style="width: 32px; height: 32px; border-radius: 50%; background: #881337; color: white; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px auto;">1</div>
                    <div style="font-size: 11px; font-weight: 800; color: #881337;">تحديد الطالب</div>
                </div>
                <div class="w-step-item" id="w-step-ind-2" style="position: relative; z-index: 2; text-align: center;">
                    <div class="w-step-num" style="width: 32px; height: 32px; border-radius: 50%; background: #cbd5e1; color: #475569; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px auto;">2</div>
                    <div style="font-size: 11px; font-weight: 800; color: #64748b;">التحقق والبيانات</div>
                </div>
                <div class="w-step-item" id="w-step-ind-3" style="position: relative; z-index: 2; text-align: center;">
                    <div class="w-step-num" style="width: 32px; height: 32px; border-radius: 50%; background: #cbd5e1; color: #475569; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px auto;">3</div>
                    <div style="font-size: 11px; font-weight: 800; color: #64748b;">التوقيع والإقرار</div>
                </div>
                <div class="w-step-item" id="w-step-ind-4" style="position: relative; z-index: 2; text-align: center;">
                    <div class="w-step-num" style="width: 32px; height: 32px; border-radius: 50%; background: #cbd5e1; color: #475569; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px auto;">4</div>
                    <div style="font-size: 11px; font-weight: 800; color: #64748b;">إرسال الطلب</div>
                </div>
            </div>

            <!-- Alert / Message Container -->
            <div id="w-alert-box" style="display: none; padding: 12px 16px; border-radius: 12px; font-size: 12.5px; font-weight: 700; margin-bottom: 18px; line-height: 1.5;"></div>

            <!-- STEP 1: SEARCH & IDENTIFY STUDENT -->
            <div id="w-panel-step-1" style="display: block;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                    <label style="font-size: 13px; font-weight: 800; color: #0f172a; display: block; margin-bottom: 6px;">أدخل اسم الطالب الكامل المسجل بالمدرسة للبحث:</label>
                    <input type="text" id="w_student_name_input" onkeyup="wDebounceSearchName()" placeholder="أدخل اسم الطالب الكامل..." style="width: 100%; height: 44px; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 0 14px; font-size: 13px; font-weight: 700; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
                    <div style="font-size: 11px; color: #64748b; margin-top: 6px;">تنبيه: تظهر اقتراحات الطالب فقط عند إدخال اسم الطالب الكامل (الثلاثي على الأقل).</div>
                </div>

                <div id="w-search-suggestions" style="display: none; margin-bottom: 18px;"></div>

                <!-- Selected Student Preview Box -->
                <div id="w-selected-stu-box" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 14px; padding: 16px; margin-bottom: 18px;">
                    <div style="font-size: 11px; color: #166534; font-weight: 800; margin-bottom: 4px;">✓ تم اختيار الطالب:</div>
                    <div style="font-size: 16px; font-weight: 900; color: #14532d; margin-bottom: 4px;" id="w_sel_stu_name"></div>
                    <div style="font-size: 12px; color: #15803d; font-weight: 700;" id="w_sel_stu_class"></div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="button" id="w_btn_next_1" disabled onclick="wGoToStep(2)" style="height: 44px; padding: 0 28px; background: #881337; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13.5px; cursor: not-allowed; opacity: 0.5;">الانتقال للخطوة التالية ➔</button>
                </div>
            </div>

            <!-- STEP 2: VERIFY STUDENT IDENTITY & UPDATE MISSING DATA -->
            <div id="w-panel-step-2" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                    <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 800; color: #0f172a;">تأكيد التحقق من هوية الطالب</h4>
                    <p style="margin: 0 0 14px 0; font-size: 12px; color: #64748b; font-weight: 600;">يرجى إدخال بيانات التحقق المطلوبة للأمان:</p>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">رمز التحقق المعتمد <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="w_verify_code_input" placeholder="أدخل كود الطالب أو الهوية الوطنية..." style="width: 100%; height: 44px; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 0 14px; font-size: 13px; font-weight: 700; box-sizing: border-box;">
                    </div>

                    <button type="button" onclick="wVerifyStudentIdentity()" id="w_btn_verify_id" style="width: 100%; height: 42px; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">تأكيد والتحقق من الهوية</button>
                </div>

                <!-- Verification Result & Student Code Display Box -->
                <div id="w-verified-status-panel" style="display: none; margin-bottom: 18px;"></div>

                <!-- DYNAMIC MISSING REQUIRED DATA COMPLETION FORM -->
                <div id="w-missing-data-container" style="display: none; background: #fffbe3; border: 1.5px solid #fde047; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                    <div style="font-size: 13.5px; font-weight: 900; color: #854d0e; margin-bottom: 8px;">
                        ⚠️ يتطلب النظام استكمال البيانات المطلوبة التالية للطالب قبل المتابعة:
                    </div>

                    <form id="w_missing_data_form" onsubmit="wSubmitMissingData(event)">
                        <div id="w_missing_fields_render_box"></div>

                        <div style="margin-top: 14px; text-align: left;">
                            <button type="submit" id="w_btn_save_missing" style="height: 42px; padding: 0 24px; background: #854d0e; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">حفظ وتحديث البيانات الحالية</button>
                        </div>
                    </form>
                </div>

                <!-- Mandatory Official Student Photo Upload Box -->
                <div id="w-photo-upload-container" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 13.5px; color: #166534; margin-bottom: 8px;">
                        <span>📷 رفع صورة شخصية رسمية معتمدة للطالب</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 12px; color: #14532d; line-height: 1.5;">
                        الملف لا يحتوي على صورة معتمدة. يرجى رفع صورة شخصية خلفية بيضاء.
                    </p>

                    <div style="margin-bottom: 10px;">
                        <input type="file" id="w_student_photo_file" accept="image/jpeg,image/png,image/webp" onchange="wValidateStudentPhoto(this)" style="width: 100%; font-size: 12px; background: white; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1;">
                    </div>

                    <div id="w_photo_preview_box" style="display: none; margin-top: 10px; background: white; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                        <img id="w_photo_preview_img" src="" style="width: 90px; height: 110px; object-fit: cover; border-radius: 8px; border: 2px solid #0f172a; margin-bottom: 6px;" alt="Student Photo Preview">
                        <div style="font-size: 11px; color: #16a34a; font-weight: 800;" id="w_photo_status_msg">✓ تم معاينة الصورة بنجاح.</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" onclick="wGoToStep(1)" style="height: 42px; padding: 0 20px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 800; font-size: 12.5px; cursor: pointer;">➔ السابق</button>
                    <button type="button" id="w_btn_next_2" disabled onclick="wGoToStep(3)" style="height: 44px; padding: 0 28px; background: #881337; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13.5px; cursor: not-allowed; opacity: 0.5;">المتابعة للإقرار والتوقيع ➔</button>
                </div>
            </div>

            <!-- STEP 3: PARENT DECLARATION & ELECTRONIC SIGNATURE -->
            <div id="w-panel-step-3" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 800; color: #0f172a;">إقرار ولي الأمر والتوقيع الإلكتروني</h4>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">اسم ولي الأمر الثلاثي <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="w_parent_name" placeholder="أدخل الاسم الكامل لولي الأمر..." style="width: 100%; height: 42px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 12.5px; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">رقم هاتف التواصل <span style="color:#ef4444;">*</span></label>
                        <div style="display: flex; align-items: center; gap: 6px; direction: ltr;">
                            <span style="background: #e2e8f0; border: 1px solid #cbd5e1; padding: 10px 12px; border-radius: 8px; font-size: 13px; font-weight: 800; color: #0f172a;">+971</span>
                            <input type="tel" id="w_parent_phone" placeholder="501234567" style="flex: 1; height: 42px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 12.5px; box-sizing: border-box; text-align: left;">
                        </div>
                    </div>

                    <div style="background: #fffbe3; border: 1px solid #fde047; border-radius: 10px; padding: 14px; font-size: 11.5px; color: #854d0e; line-height: 1.6; margin-bottom: 14px;">
                        <strong>تعهد وإقرار ولي الأمر الرسمي:</strong><br>
                        أقر أنا ولي أمر الطالب المذكور أعلاه بطلبي الرسمي لإصدار بطاقة تصريح الخروج الرقمية للطالب. وأتحمل المسؤولية الكاملة عن خروج الطالب واستئذانه بموجب هذا التصريح وإقرار بإخلاء طرف إدارة المدرسة.
                    </div>

                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">
                        <input type="checkbox" id="w_declaration_chk" onchange="wCheckStep3Valid()" style="width: 18px; height: 18px; margin-top: 1px;">
                        <span>أقر وأوافق على كافة الشروط والالتزامات الواردة في الإقرار أعلاه *</span>
                    </label>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 12px; font-weight: 800; color: #0f172a;">التوقيع الإلكتروني لولي الأمر <span style="color:#ef4444;">*</span></label>
                            <button type="button" onclick="wClearSignature()" style="background: #fee2e2; color: #991b1b; border: none; padding: 2px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer;">مسح التوقيع ↺</button>
                        </div>
                        <div style="border: 2px dashed #cbd5e1; border-radius: 12px; background: #ffffff; overflow: hidden; touch-action: none;">
                            <canvas id="w-signature-pad" width="600" height="150" style="width: 100%; height: 140px; display: block; cursor: crosshair;"></canvas>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" onclick="wGoToStep(2)" style="height: 42px; padding: 0 20px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 800; font-size: 12.5px; cursor: pointer;">➔ السابق</button>
                    <button type="button" id="w_btn_next_3" disabled onclick="wGoToStep(4)" style="height: 44px; padding: 0 28px; background: #881337; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13.5px; cursor: not-allowed; opacity: 0.5;">مراجعة ملخص الطلب ➔</button>
                </div>
            </div>

            <!-- STEP 4: FINAL SUMMARY REVIEW & SUBMIT -->
            <div id="w-panel-step-4" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                    <h4 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 900; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">ملخص وتأكيد طلب تصريح الخروج</h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12px; color: #334155; line-height: 1.6; margin-bottom: 14px;">
                        <div><strong>اسم الطالب:</strong> <span id="w_sum_stu_name" style="color: #0f172a; font-weight: 800;">---</span></div>
                        <div><strong>الصف والشعبة:</strong> <span id="w_sum_stu_class" style="color: #0f172a; font-weight: 800;">---</span></div>
                        <div><strong>كود الطالب:</strong> <span id="w_sum_stu_code" style="color: #881337; font-weight: 800;">---</span></div>
                        <div><strong>ولي الأمر:</strong> <span id="w_sum_parent_name" style="color: #0f172a; font-weight: 800;">---</span></div>
                        <div><strong>هاتف التواصل:</strong> <span id="w_sum_parent_phone" style="color: #0f172a; font-weight: 800;">---</span></div>
                        <div><strong>تاريخ الطلب:</strong> <span style="color: #0f172a; font-weight: 800;"><?php echo current_time('Y-m-d'); ?></span></div>
                    </div>

                    <div style="border-top: 1px solid #e2e8f0; padding-top: 10px;">
                        <div style="font-size: 11.5px; font-weight: 800; color: #64748b; margin-bottom: 4px;">معاينة التوقيع الإلكتروني المعتمد:</div>
                        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px; text-align: center;">
                            <img id="w_sum_sig_img" src="" style="max-height: 60px; object-fit: contain;" alt="Signature Preview">
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" onclick="wGoToStep(3)" style="height: 42px; padding: 0 20px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 800; font-size: 12.5px; cursor: pointer;">➔ تعديل البيانات</button>
                    <button type="button" id="w_btn_submit_final" onclick="wSubmitExitCardFinal()" style="height: 46px; padding: 0 32px; background: #16a34a; color: white; border: none; border-radius: 10px; font-weight: 900; font-size: 14px; cursor: pointer; box-shadow: 0 4px 12px rgba(22,163,74,0.25);">تأكيد وإرسال الطلب النهائي ✓</button>
                </div>
            </div>

            <!-- SUBMISSION SUCCESS CONFIRMATION SCREEN -->
            <div id="w-panel-success" style="display: none; text-align: center; padding: 20px 10px;">
                <div style="width: 64px; height: 64px; background: #dcfce7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #16a34a; margin-bottom: 14px; font-size: 28px; font-weight: 900;">✓</div>
                <h3 style="margin: 0 0 6px 0; font-size: 20px; font-weight: 900; color: #15803d;">تم تقديم طلب تصريح الخروج بنجاح</h3>
                <p style="font-size: 13px; color: #475569; margin: 0 0 16px 0;">تم تسجيل الطلب بالنظام وهو الآن قيد المراجعة المباشرة من قسم شؤون الطلاب.</p>

                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px; padding: 16px; max-width: 380px; margin: 0 auto 20px auto;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; margin-bottom: 2px;">الرقم المرجعي للطلب:</div>
                    <div style="font-size: 22px; font-weight: 900; color: #881337; font-family: monospace; letter-spacing: 1px;" id="w_success_ref_no">EXT-2026-00000</div>
                </div>

                <button type="button" onclick="location.reload()" style="height: 42px; padding: 0 26px; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">الرجوع للصفحة الرئيسية</button>
            </div>
        </div>

        <!-- VIEW 2: REQUIRED DATA FOR UPDATE (SETTINGS VIEW - 2 PER ROW GRID) -->
        <div id="pv-view-required-data" style="display: none;">
            <div style="border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px;">
                <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 900; color: #0f172a;">📝 البيانات المطلوبة للتحديث</h3>
                <div style="font-size: 12px; color: #64748b;">حدد حقول بيانات الطالب المطلوب للطلاب/أولياء الأمور استكمالها عند وجود نقص بالسجل:</div>
            </div>

            <form onsubmit="wSavePortalSettingsFromView(event)">
                <input type="hidden" name="portal_mode" value="<?php echo esc_attr($portal_mode); ?>">
                <input type="hidden" name="verify_method" value="<?php echo esc_attr($verify_method); ?>">

                <div class="eess-req-fields-grid" style="margin-bottom: 20px;">
                    <?php
                    $available_fields = array(
                        'guardian_phone' => 'رقم هاتف ولي الأمر (+971)',
                        'dob' => 'تاريخ الميلاد',
                        'gender' => 'الجنس',
                        'guardian_name' => 'اسم ولي الأمر الثلاثي',
                        'emirate' => 'إمارة السكن',
                        'address' => 'العنوان السكني التفصيلي',
                        'nationality' => 'الجنسية',
                        'national_id' => 'رقم الهوية الوطنية'
                    );
                    foreach ($available_fields as $fk => $flabel):
                        $chk = in_array($fk, $required_fields) ? 'checked' : '';
                    ?>
                        <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; cursor: pointer; font-size: 12.5px; font-weight: 800; color: #0f172a; transition: border-color 0.2s;">
                            <input type="checkbox" name="required_fields[]" value="<?php echo esc_attr($fk); ?>" <?php echo $chk; ?> style="width: 18px; height: 18px;">
                            <span><?php echo esc_html($flabel); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?php if ($is_admin): ?>
                    <button type="submit" style="height: 44px; padding: 0 28px; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">حفظ البيانات المطلوبة</button>
                <?php else: ?>
                    <div style="font-size: 12px; color: #991b1b; background: #fef2f2; padding: 10px; border-radius: 8px;">تنبيه: تعديل الإعدادات متاح فقط لمشرفي النظام.</div>
                <?php endif; ?>
            </form>
        </div>

        <!-- VIEW 3: PORTAL OPERATING MODE (SETTINGS VIEW) -->
        <div id="pv-view-operating-mode" style="display: none;">
            <div style="border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px;">
                <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 900; color: #0f172a;">⚙️ نمط عمل البوابة والتحقق</h3>
                <div style="font-size: 12px; color: #64748b;">اختر طريقة وطبيعة تقديم الخدمة وآلية التحقق للمستخدمين عبر هذا الرابط:</div>
            </div>

            <form onsubmit="wSavePortalSettingsFromView(event)">
                <!-- Preserve existing required fields -->
                <?php foreach ($required_fields as $rf): ?>
                    <input type="hidden" name="required_fields[]" value="<?php echo esc_attr($rf); ?>">
                <?php endforeach; ?>

                <!-- Portal Operating Mode -->
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 13px; font-weight: 900; color: #0f172a; display: block; margin-bottom: 8px;">1. نمط تقديم الخدمة:</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px; background: #f8fafc; border: 1.5px solid <?php echo ($portal_mode === 'card_application') ? '#881337' : '#e2e8f0'; ?>; border-radius: 12px; padding: 14px; cursor: pointer;">
                            <input type="radio" name="portal_mode" value="card_application" <?php checked($portal_mode, 'card_application'); ?> style="width: 18px; height: 18px; margin-top: 2px;">
                            <div>
                                <div style="font-size: 13.5px; font-weight: 900; color: #0f172a;">تقديم بطاقات تصريح الخروج + استكمال البيانات</div>
                                <div style="font-size: 11.5px; color: #64748b;">تقديم طلب تصريح خروج رسمي مع استكمال أي بيانات مفقودة.</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; background: #f8fafc; border: 1.5px solid <?php echo ($portal_mode === 'update_only') ? '#881337' : '#e2e8f0'; ?>; border-radius: 14px; padding: 14px; cursor: pointer;">
                            <input type="radio" name="portal_mode" value="update_only" <?php checked($portal_mode, 'update_only'); ?> style="width: 18px; height: 18px; margin-top: 2px;">
                            <div>
                                <div style="font-size: 13.5px; font-weight: 900; color: #0f172a;">تحديث بيانات الطالب فقط (Data Update Only)</div>
                                <div style="font-size: 11.5px; color: #64748b;">استكمال البيانات المطلوبة فقط دون فتح خيار تقديم بطاقات الخروج.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Verification Step Settings -->
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 13px; font-weight: 900; color: #0f172a; display: block; margin-bottom: 8px;">2. طريقة التحقق من الهوية (الخطوة الثانية):</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 12.5px; font-weight: 800;">
                            <input type="radio" name="verify_method" value="both" <?php checked($verify_method, 'both'); ?> style="width: 18px; height: 18px;">
                            <span>كود الطالب أو رقم الهوية الوطنية (كلاهما مقبول)</span>
                        </label>

                        <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 12.5px; font-weight: 800;">
                            <input type="radio" name="verify_method" value="code" <?php checked($verify_method, 'code'); ?> style="width: 18px; height: 18px;">
                            <span>كود الطالب فقط (Student Code Only)</span>
                        </label>

                        <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 12.5px; font-weight: 800;">
                            <input type="radio" name="verify_method" value="nat_id" <?php checked($verify_method, 'nat_id'); ?> style="width: 18px; height: 18px;">
                            <span>رقم الهوية الوطنية فقط (National ID Only)</span>
                        </label>
                    </div>
                </div>

                <?php if ($is_admin): ?>
                    <button type="submit" style="height: 44px; padding: 0 28px; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">حفظ الإعدادات والضوابط</button>
                <?php else: ?>
                    <div style="font-size: 12px; color: #991b1b; background: #fef2f2; padding: 10px; border-radius: 8px;">تنبيه: تعديل الإعدادات متاح فقط لمشرفي النظام.</div>
                <?php endif; ?>
            </form>
        </div>

        <!-- VIEW 4: EXIT CARD REQUESTS MANAGEMENT (2 CARDS PER ROW GRID) -->
        <div id="pv-view-requests-management" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 900; color: #0f172a;">📋 إدارة طلبات تصاريح الخروج</h3>
                    <div style="font-size: 12px; color: #64748b;">متابعة، مراجعة، والتحقق من طلبات تصاريح الخروج المقدمة عبر البوابة:</div>
                </div>
                <button type="button" onclick="wLoadCardRequestsFull()" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer;">تحديث القائمة ↺</button>
            </div>

            <!-- List of Visual Cards for Requests (2 Cards Per Row) -->
            <div id="pv-requests-cards-container" class="eess-req-cards-grid">
                <div style="text-align: center; color: #64748b; padding: 20px; font-size: 13px; grid-column: span 2;">جاري تحميل طلبات تصاريح الخروج...</div>
            </div>
        </div>

    </div>

    <!-- RIGHT NAVIGATION SIDEBAR (Positioned on RIGHT with Professional SVG Icons) -->
    <div id="eess-portal-nav-sidebar" class="eess-portal-right-sidebar">

        <div style="font-size: 14px; font-weight: 900; color: #0f172a; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
            <span>القائمة العامة للبوابة</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;" id="sb-nav-items-list">
            <!-- Item 1: Service Wizard -->
            <button type="button" class="sb-nav-btn active" onclick="wSwitchPortalView('pv-view-wizard', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                <span>تقديم طلب / تحديد الطالب</span>
            </button>

            <!-- Item 2: Required Data for Update -->
            <button type="button" class="sb-nav-btn" onclick="wSwitchPortalView('pv-view-required-data', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                <span>البيانات المطلوبة للتحديث</span>
            </button>

            <!-- Item 3: Portal Operating Mode -->
            <button type="button" class="sb-nav-btn" onclick="wSwitchPortalView('pv-view-operating-mode', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <span>نمط عمل البوابة والتحقق</span>
            </button>

            <!-- Item 4: Exit Permit Requests -->
            <button type="button" class="sb-nav-btn" onclick="wSwitchPortalView('pv-view-requests-management', this)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M7 15h0M2 9.5h20"></path></svg>
                <span>طلبات تصاريح الخروج</span>
            </button>
        </div>

        <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid #f1f5f9; font-size: 11px; color: #64748b; line-height: 1.5; text-align: center;">
            النمط الحالي: <strong style="color:#0f172a;"><?php echo ($portal_mode === 'update_only') ? 'تحديث فقط' : 'تصاريح الخروج'; ?></strong>
        </div>

    </div>

</div>

<!-- VIEW REQUEST DETAILS MODAL -->
<div id="w-req-view-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 15px; box-sizing: border-box;">
    <div style="background: white; border-radius: 18px; max-width: 520px; width: 100%; padding: 22px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); direction: rtl; font-family: 'Cairo', sans-serif;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 14px;">
            <h4 style="margin: 0; font-size: 16px; font-weight: 900; color: #0f172a;" id="modal_req_title">تفاصيل طلب تصريح الخروج</h4>
            <button type="button" onclick="document.getElementById('w-req-view-modal').style.display='none'" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b;">✕</button>
        </div>
        <div id="modal_req_body" style="font-size: 12.5px; color: #334155; line-height: 1.7;"></div>
    </div>
</div>

<!-- IN-SYSTEM DELETE CONFIRMATION MODAL -->
<div id="eess-delete-confirm-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.65); z-index: 999999; align-items: center; justify-content: center; padding: 15px; box-sizing: border-box;">
    <div style="background: white; border-radius: 20px; max-width: 440px; width: 100%; padding: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); direction: rtl; font-family: 'Cairo', sans-serif; text-align: center;">
        <div style="width: 56px; height: 56px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; color: #dc2626;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </div>
        <h3 style="margin: 0 0 6px 0; font-size: 17px; font-weight: 900; color: #0f172a;">تأكيد حذف طلب تصريح الخروج</h3>
        <p style="margin: 0 0 14px 0; font-size: 13px; color: #475569; line-height: 1.5;">
            هل أنت أكر من حذف هذا الطلب للطالب <strong id="eess_del_student_name" style="color: #881337;">---</strong>؟
        </p>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; font-size: 11.5px; color: #64748b; margin-bottom: 18px; line-height: 1.5;">
            تنبيه أمني: سيتم حذف بيانات الطلب فقط، ولن يتم تعديل أو حذف سجل الطالب الرئيسي بقاعدة البيانات.
        </div>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" onclick="wCloseDeleteModal()" style="height: 40px; padding: 0 22px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; font-weight: 800; font-size: 12.5px; cursor: pointer;">إلغاء</button>
            <button type="button" id="eess_btn_confirm_delete" onclick="wExecuteConfirmDelete()" style="height: 40px; padding: 0 22px; background: #dc2626; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 12.5px; cursor: pointer;">تأكيد حذف الطلب</button>
        </div>
    </div>
</div>

<script>
let wCurrentStep = 1;
let wSelectedStudent = null;
let wVerifiedData = null;
let wSearchTimeout = null;
let wSubmitting = false;
let wPendingDeleteReqId = null;

// Signature Canvas
let wCanvas = null;
let wCtx = null;
let wIsDrawing = false;

document.addEventListener('DOMContentLoaded', function() {
    wCanvas = document.getElementById('w-signature-pad');
    if (wCanvas) {
        wCtx = wCanvas.getContext('2d');
        wCtx.strokeStyle = '#0f172a';
        wCtx.lineWidth = 2.5;
        wCtx.lineCap = 'round';

        function getPos(e) {
            const rect = wCanvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (wCanvas.width / rect.width),
                y: (clientY - rect.top) * (wCanvas.height / rect.height)
            };
        }

        wCanvas.addEventListener('mousedown', function(e) { wIsDrawing = true; const p = getPos(e); wCtx.beginPath(); wCtx.moveTo(p.x, p.y); });
        wCanvas.addEventListener('mousemove', function(e) { if (!wIsDrawing) return; const p = getPos(e); wCtx.lineTo(p.x, p.y); wCtx.stroke(); wCheckStep3Valid(); });
        wCanvas.addEventListener('mouseup', function() { wIsDrawing = false; });
        wCanvas.addEventListener('mouseleave', function() { wIsDrawing = false; });

        wCanvas.addEventListener('touchstart', function(e) { e.preventDefault(); wIsDrawing = true; const p = getPos(e); wCtx.beginPath(); wCtx.moveTo(p.x, p.y); });
        wCanvas.addEventListener('touchmove', function(e) { e.preventDefault(); if (!wIsDrawing) return; const p = getPos(e); wCtx.lineTo(p.x, p.y); wCtx.stroke(); wCheckStep3Valid(); });
        wCanvas.addEventListener('touchend', function() { wIsDrawing = false; });
    }
});

function eessShowToast(message, type) {
    const toast = document.getElementById('eess-toast-notification');
    const msgEl = document.getElementById('eess-toast-message');
    const iconEl = document.getElementById('eess-toast-icon');

    if (!toast) return;

    msgEl.innerText = message;
    if (type === 'error') {
        toast.style.borderRightColor = '#ef4444';
        iconEl.innerText = '✕';
    } else {
        toast.style.borderRightColor = '#16a34a';
        iconEl.innerText = '✓';
    }

    toast.style.display = 'flex';
    setTimeout(function() {
        toast.style.display = 'none';
    }, 3500);
}

function wSwitchPortalView(viewId, btnEl) {
    document.querySelectorAll('#pv-view-wizard, #pv-view-required-data, #pv-view-operating-mode, #pv-view-requests-management').forEach(el => {
        el.style.display = 'none';
    });
    const target = document.getElementById(viewId);
    if (target) target.style.display = 'block';

    document.querySelectorAll('.sb-nav-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = '#f8fafc';
        btn.style.color = '#334155';
        btn.style.border = '1px solid #e2e8f0';
    });
    if (btnEl) {
        btnEl.classList.add('active');
        btnEl.style.background = '#881337';
        btnEl.style.color = '#ffffff';
        btnEl.style.border = 'none';
    }

    if (viewId === 'pv-view-requests-management') {
        wLoadCardRequestsFull();
    }
}

function wClearSignature() {
    if (wCtx && wCanvas) {
        wCtx.clearRect(0, 0, wCanvas.width, wCanvas.height);
        wCheckStep3Valid();
    }
}

function wIsCanvasBlank() {
    if (!wCanvas) return true;
    const pixelBuffer = new Uint32Array(wCtx.getImageData(0, 0, wCanvas.width, wCanvas.height).data.buffer);
    return !pixelBuffer.some(color => color !== 0);
}

function wDebounceSearchName() {
    clearTimeout(wSearchTimeout);
    wSearchTimeout = setTimeout(wSearchStudentName, 300);
}

function wSearchStudentName() {
    const val = document.getElementById('w_student_name_input').value.trim();
    const suggestions = document.getElementById('w-search-suggestions');
    const alertBox = document.getElementById('w-alert-box');
    alertBox.style.display = 'none';

    const words = val.split(' ').filter(w => w.length > 0);
    // Suppress suggestions until complete full student name (3+ words) is typed
    if (words.length < 3) {
        suggestions.style.display = 'none';
        return;
    }

    suggestions.style.display = 'block';
    suggestions.innerHTML = '<div style="text-align:center; padding:10px; font-weight:700; color:#64748b; font-size:12px;">جاري البحث عن تطابقات...</div>';

    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_public_search_student',
        name_query: val
    }, function(res) {
        if (res.success && res.data && res.data.length > 0) {
            let html = '<div style="background:#ffffff; border:1px solid #cbd5e1; border-radius:10px; max-height:220px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.05);">';
            res.data.forEach(s => {
                html += '<div onclick="wSelectStudent(' + s.id + ', \'' + s.display_name.replace(/'/g, "\\'") + '\', \'' + s.class_name + '\', \'' + s.section + '\')" style="padding:10px 14px; border-bottom:1px solid #f1f5f9; cursor:pointer; font-size:12.5px; font-weight:800; color:#0f172a; transition:background 0.2s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'white\'">';
                html += '<div>' + s.display_name + '</div>';
                html += '<div style="font-size:11px; color:#64748b; font-weight:600;">' + s.class_name + ' (' + s.section + ')</div>';
                html += '</div>';
            });
            html += '</div>';
            suggestions.innerHTML = html;
        } else {
            suggestions.innerHTML = '<div style="background:#fef2f2; border:1px solid #fecdd3; border-radius:10px; padding:12px; color:#991b1b; font-size:12px; font-weight:700; text-align:center;">' + (res.data || 'لم يتم العثور على طالب يطابق الاسم الكامل المدخل.') + '</div>';
        }
    });
}

function wSelectStudent(id, displayName, className, section) {
    wSelectedStudent = { id: id, name: displayName, class_name: className, section: section };
    document.getElementById('w_sel_stu_name').innerText = displayName;
    document.getElementById('w_sel_stu_class').innerText = 'الصف: ' + className + ' (' + section + ')';
    document.getElementById('w-selected-stu-box').style.display = 'block';
    document.getElementById('w-search-suggestions').style.display = 'none';

    const btnNext = document.getElementById('w_btn_next_1');
    btnNext.disabled = false;
    btnNext.style.opacity = '1';
    btnNext.style.cursor = 'pointer';
}

function wVerifyStudentIdentity() {
    const codeVal = document.getElementById('w_verify_code_input').value.trim();
    const alertBox = document.getElementById('w-alert-box');
    const panel = document.getElementById('w-verified-status-panel');
    const missingBox = document.getElementById('w-missing-data-container');
    const photoContainer = document.getElementById('w-photo-upload-container');

    alertBox.style.display = 'none';
    panel.style.display = 'none';
    missingBox.style.display = 'none';
    photoContainer.style.display = 'none';

    if (!codeVal) {
        eessShowToast('يرجى إدخال رمز التحقق المطلوبة.', 'error');
        return;
    }

    const btn = document.getElementById('w_btn_verify_id');
    btn.disabled = true;
    btn.innerText = 'جاري التحقق...';

    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_public_verify_student',
        student_id: wSelectedStudent.id,
        verify_code: codeVal
    }, function(res) {
        btn.disabled = false;
        btn.innerText = 'تأكيد والتحقق من الهوية';

        if (res.success && res.data) {
            wVerifiedData = res.data;
            const btnNext = document.getElementById('w_btn_next_2');

            let html = '<div style="background:#ffffff; border:1px solid #cbd5e1; border-radius:14px; padding:16px;">';
            html += '<div style="font-size:13px; font-weight:800; color:#16a34a; margin-bottom:8px;">✓ تم التحقق بنجاح من هويّة الطالب</div>';

            // Prominent Student Code Highlight Box for Student Reference
            html += '<div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:12px; padding:12px; margin-bottom:12px;">';
            html += '<div style="font-size:11px; color:#166534; font-weight:800;">📌 كود الطالب المعتمد الخاص بك:</div>';
            html += '<div style="font-size:20px; font-weight:900; color:#881337; font-family:monospace; margin:2px 0;">' + res.data.student.student_code + '</div>';
            html += '<div style="font-size:11px; color:#15803d; font-weight:700;">يرجى تدوين وحفظ كود الطالب لاستخدامه في متابعة الطلب والخدمات المدرسية القادمة.</div>';
            html += '</div>';

            if (res.data.missing_fields && res.data.missing_fields.length > 0) {
                wRenderMissingFieldsForm(res.data.missing_fields, res.data.student);
                missingBox.style.display = 'block';
                btnNext.disabled = true;
                btnNext.style.opacity = '0.5';
                btnNext.style.cursor = 'not-allowed';
            } else {
                if (res.data.portal_mode === 'update_only') {
                    html += '<div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px; color:#166534; font-weight:800; font-size:12.5px; margin-top:8px;">';
                    html += '✓ كافة بيانات الطالب الحالية مكتملة ومحدثة بنجاح بالنظام.';
                    html += '</div>';
                    btnNext.disabled = true;
                    btnNext.style.opacity = '0.5';
                } else {
                    if (!res.data.has_photo) {
                        photoContainer.style.display = 'block';
                        btnNext.disabled = true;
                        btnNext.style.opacity = '0.5';
                    } else if (!res.data.active_request && !res.data.exceeded_limit) {
                        btnNext.disabled = false;
                        btnNext.style.opacity = '1';
                        btnNext.style.cursor = 'pointer';
                    }
                }
            }

            if (res.data.active_request) {
                const ar = res.data.active_request;
                html += '<div style="background:#fefce8; border:1px solid #fef08a; border-radius:10px; padding:12px; margin-top:10px;">';
                html += '<div style="font-weight:900; color:#854d0e; font-size:12.5px;">تنبيه: يوجد طلب نشط قائم برقم (' + ar.reference_no + ')</div>';
                html += '<div style="font-size:11.5px; color:#a16207; margin-top:2px;"><strong>الحالة:</strong> ' + ar.status_label + '</div>';
                html += '</div>';
                btnNext.disabled = true;
                btnNext.style.opacity = '0.5';
            }

            html += '</div>';
            panel.innerHTML = html;
            panel.style.display = 'block';

        } else {
            eessShowToast(res.data || 'رمز التحقق غير مطابق للبيانات المسجلة.', 'error');
        }
    });
}

function wRenderMissingFieldsForm(missingFields, student) {
    const box = document.getElementById('w_missing_fields_render_box');
    let html = '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; text-align:right;">';

    missingFields.forEach(fk => {
        if (fk === 'guardian_phone') {
            html += '<div style="grid-column: span 2;">';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">رقم هاتف ولي الأمر (الإمارات +971) <span style="color:#ef4444;">*</span></label>';
            html += '<div style="display:flex; align-items:center; gap:6px; direction:ltr;">';
            html += '<span style="background:#e2e8f0; border:1px solid #cbd5e1; padding:10px 12px; border-radius:8px; font-size:13px; font-weight:800; color:#0f172a;">+971</span>';
            html += '<input type="tel" name="guardian_phone" placeholder="501234567" required style="flex:1; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 12px; font-size:12.5px; text-align:left;">';
            html += '</div></div>';
        } else if (fk === 'dob') {
            html += '<div>';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">تاريخ الميلاد <span style="color:#ef4444;">*</span></label>';
            html += '<input type="date" name="dob" required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:12px; box-sizing:border-box;">';
            html += '</div>';
        } else if (fk === 'gender') {
            html += '<div>';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">الجنس <span style="color:#ef4444;">*</span></label>';
            html += '<select name="gender" required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:12px; box-sizing:border-box;">';
            html += '<option value="ذكر">ذكر</option><option value="أنثى">أنثى</option>';
            html += '</select></div>';
        } else if (fk === 'guardian_name') {
            html += '<div>';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">اسم ولي الأمر <span style="color:#ef4444;">*</span></label>';
            html += '<input type="text" name="guardian_name" placeholder="الاسم الثلاثي..." required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:12px; box-sizing:border-box;">';
            html += '</div>';
        } else if (fk === 'emirate') {
            html += '<div>';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">إمارة السكن <span style="color:#ef4444;">*</span></label>';
            html += '<input type="text" name="emirate" placeholder="الشارقة / دبي..." required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:12px; box-sizing:border-box;">';
            html += '</div>';
        } else if (fk === 'address') {
            html += '<div style="grid-column: span 2;">';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">العنوان السكني <span style="color:#ef4444;">*</span></label>';
            html += '<input type="text" name="address" placeholder="المنطقة، الشارع..." required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:12px; box-sizing:border-box;">';
            html += '</div>';
        } else if (fk === 'nationality') {
            html += '<div>';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">الجنسية <span style="color:#ef4444;">*</span></label>';
            html += '<input type="text" name="nationality" placeholder="الجنسية..." required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:12px; box-sizing:border-box;">';
            html += '</div>';
        } else if (fk === 'national_id') {
            html += '<div>';
            html += '<label style="font-size:12px; font-weight:800; color:#0f172a; display:block; margin-bottom:4px;">رقم الهوية الوطنية <span style="color:#ef4444;">*</span></label>';
            html += '<input type="text" name="national_id" placeholder="784-XXXX-XXXXXXX-X" required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:12px; box-sizing:border-box;">';
            html += '</div>';
        }
    });

    html += '</div>';
    box.innerHTML = html;
}

function wSubmitMissingData(e) {
    e.preventDefault();
    const btn = document.getElementById('w_btn_save_missing');
    btn.disabled = true;
    btn.innerText = 'جاري الحفظ...';

    const form = document.getElementById('w_missing_data_form');
    const formData = new FormData(form);
    formData.append('action', 'sm_public_update_student_missing_data');
    formData.append('student_id', wSelectedStudent.id);
    formData.append('verify_code', document.getElementById('w_verify_code_input').value.trim());

    jQuery.ajax({
        url: '<?php echo $ajax_url; ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            btn.disabled = false;
            btn.innerText = 'حفظ وتحديث البيانات الحالية';

            if (res.success) {
                eessShowToast(res.data.message || 'تم حفظ وتحديث البيانات بنجاح.', 'success');
                document.getElementById('w-missing-data-container').style.display = 'none';
                wVerifyStudentIdentity();
            } else {
                eessShowToast(res.data || 'فشل حفظ البيانات.', 'error');
            }
        },
        error: function() {
            btn.disabled = false;
            btn.innerText = 'حفظ وتحديث البيانات الحالية';
            eessShowToast('حدث خطأ في الاتصال بالسيرفر.', 'error');
        }
    });
}

function wValidateStudentPhoto(input) {
    const previewBox = document.getElementById('w_photo_preview_box');
    const previewImg = document.getElementById('w_photo_preview_img');
    const btnNext    = document.getElementById('w_btn_next_2');

    if (!input.files || !input.files[0]) {
        previewBox.style.display = 'none';
        return;
    }

    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        previewImg.src = e.target.result;
        previewBox.style.display = 'block';

        if (wVerifiedData && !wVerifiedData.active_request && !wVerifiedData.exceeded_limit) {
            btnNext.disabled = false;
            btnNext.style.opacity = '1';
            btnNext.style.cursor = 'pointer';
        }
    };
    reader.readAsDataURL(file);
}

function wCheckStep3Valid() {
    const name = document.getElementById('w_parent_name').value.trim();
    const phone = document.getElementById('w_parent_phone').value.trim();
    const chk = document.getElementById('w_declaration_chk').checked;
    const isSigned = !wIsCanvasBlank();

    const btn = document.getElementById('w_btn_next_3');
    if (name.length >= 3 && phone.length >= 7 && chk && isSigned) {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    }
}

jQuery(document).on('input change', '#w_parent_name, #w_parent_phone, #w_declaration_chk', function() {
    wCheckStep3Valid();
});

function wGoToStep(stepNum) {
    wCurrentStep = stepNum;
    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('w-panel-step-' + i);
        if (el) el.style.display = (i === stepNum) ? 'block' : 'none';

        const ind = document.getElementById('w-step-ind-' + i);
        if (ind) {
            const numBox = ind.querySelector('.w-step-num');
            if (i <= stepNum) {
                numBox.style.background = '#881337';
                numBox.style.color = 'white';
            } else {
                numBox.style.background = '#cbd5e1';
                numBox.style.color = '#475569';
            }
        }
    }

    const linePct = ((stepNum - 1) / 3) * 80;
    document.getElementById('w-progress-line').style.width = linePct + '%';

    if (stepNum === 4 && wVerifiedData && wVerifiedData.student) {
        document.getElementById('w_sum_stu_name').innerText = wVerifiedData.student.name;
        document.getElementById('w_sum_stu_class').innerText = wVerifiedData.student.class_name + ' (' + wVerifiedData.student.section + ')';
        document.getElementById('w_sum_stu_code').innerText = wVerifiedData.student.student_code;
        document.getElementById('w_sum_parent_name').innerText = document.getElementById('w_parent_name').value.trim();
        document.getElementById('w_sum_parent_phone').innerText = '+971 ' + document.getElementById('w_parent_phone').value.trim();
        if (wCanvas) {
            document.getElementById('w_sum_sig_img').src = wCanvas.toDataURL();
        }
    }
}

function wSubmitExitCardFinal() {
    if (wSubmitting) return;

    const btn = document.getElementById('w_btn_submit_final');
    wSubmitting = true;
    btn.disabled = true;
    btn.innerHTML = 'جاري إرسال وتوثيق الطلب...';

    const formData = new FormData();
    formData.append('action', 'sm_public_submit_exit_card');
    formData.append('student_id', wSelectedStudent.id);
    formData.append('verify_code', document.getElementById('w_verify_code_input').value.trim());
    formData.append('parent_name', document.getElementById('w_parent_name').value.trim());
    formData.append('parent_phone', '+971 ' + document.getElementById('w_parent_phone').value.trim());
    formData.append('declaration', document.getElementById('w_declaration_chk').checked ? 1 : 0);
    formData.append('signature_data', wCanvas ? wCanvas.toDataURL() : '');

    const photoInput = document.getElementById('w_student_photo_file');
    if (photoInput && photoInput.files && photoInput.files[0]) {
        formData.append('student_photo', photoInput.files[0]);
    }

    jQuery.ajax({
        url: '<?php echo $ajax_url; ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            wSubmitting = false;
            if (res.success && res.data) {
                document.getElementById('w-panel-step-4').style.display = 'none';
                document.getElementById('w-progress-bar').style.display = 'none';
                document.getElementById('w_success_ref_no').innerText = res.data.reference_no;
                document.getElementById('w-panel-success').style.display = 'block';
                eessShowToast('تم تقديم طلب تصريح الخروج بنجاح.', 'success');
            } else {
                btn.disabled = false;
                btn.innerHTML = 'تأكيد وإرسال الطلب النهائي ✓';
                eessShowToast('حدث خطأ أثناء إرسال الطلب: ' + (res.data || 'فشل الحفظ'), 'error');
            }
        },
        error: function() {
            wSubmitting = false;
            btn.disabled = false;
            btn.innerHTML = 'تأكيد وإرسال الطلب النهائي ✓';
            eessShowToast('حدث خطأ في الاتصال بالسيرفر أثناء إرسال الطلب.', 'error');
        }
    });
}

function wSavePortalSettingsFromView(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'sm_save_exit_card_settings');
    formData.append('nonce', '<?php echo $admin_nonce; ?>');

    jQuery.ajax({
        url: '<?php echo $ajax_url; ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.success) {
                eessShowToast(res.data.message || 'تم حفظ الإعدادات بنجاح.', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                eessShowToast('خطأ: ' + (res.data || 'فشل الحفظ'), 'error');
            }
        }
    });
}

function wLoadCardRequestsFull() {
    const box = document.getElementById('pv-requests-cards-container');
    if (!box) return;

    // Cache-buster timestamp _ts included to guarantee fresh data on refresh
    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_manage_card_requests',
        action_type: 'list',
        nonce: '<?php echo $admin_nonce; ?>',
        _ts: Date.now()
    }, function(res) {
        if (res.success && res.data && res.data.length > 0) {
            let html = '';
            res.data.forEach(r => {
                let badgeBg = '#fef08a';
                let badgeColor = '#854d0e';
                if (r.status === 'approved') { badgeBg = '#dcfce7'; badgeColor = '#166534'; }
                else if (r.status === 'rejected') { badgeBg = '#fee2e2'; badgeColor = '#991b1b'; }

                let vbadgeBg = '#fef3c7';
                let vbadgeColor = '#92400e';
                if (r.verification_status === 'parent_confirmed') { vbadgeBg = '#d1fae5'; vbadgeColor = '#065f46'; }
                else if (r.verification_status === 'parent_not_confirmed') { vbadgeBg = '#fee2e2'; vbadgeColor = '#991b1b'; }

                // Build exact formal Arabic WhatsApp message with disclaimer release text
                let waText = "السيد/السيدة ولي أمر الطالب/ة المحترم/ة،\n\n";
                waText += "نحيطكم علمًا بأنه تم تقديم طلب إصدار بطاقة تصريح خروج للطالب/ة " + r.student_name + "، كود الطالب " + r.student_code + ".\n\n";
                waText += "ونظرًا لأن إصدار بطاقة تصريح الخروج يتطلب التأكد من موافقة ولي الأمر، نرجو منكم التكرم بتأكيد موافقتكم وتحمل المسؤولية الكاملة عن الطالب/ة خارج أسوار المدرسة وإخلاء طرف إدارة المدرسة وكوادرها وفق الأنظمة المعتمدة.\n\n";
                waText += "في حال موافقتكم، يرجى الرد على هذه الرسالة بالنص التالي:\n\n";
                waText += '\"أؤكد أنني ولي أمر الطالب/ة المذكور/ة أعلاه، وأوافق على إصدار بطاقة تصريح الخروج وتحمل المسؤولية الكاملة عن خروجه/ها خارج المدرسة والإقرار بإخلاء طرف إدارة المدرسة.\"\n\n';
                waText += "وفي حال عدم تقديمكم للطلب أو عدم موافقتكم عليه، يرجى توضيح ذلك في الرد.\n\n";
                waText += "شاكرين لكم تعاونكم وتأكيدكم.\n\n";
                waText += "مع خالص التقدير والاحترام.";

                let waUrl = 'https://api.whatsapp.com/send?phone=' + encodeURIComponent(r.wa_phone) + '&text=' + encodeURIComponent(waText);

                html += '<div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:16px; padding:14px; box-shadow:0 4px 12px rgba(0,0,0,0.03); display:flex; flex-direction:column; justify-content:space-between; gap:10px;">';

                html += '<div>';
                html += '<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:6px; margin-bottom:6px;">';
                html += '<div>';
                html += '<div style="font-size:14px; font-weight:900; color:#0f172a; line-height:1.3;">' + r.student_name + '</div>';
                html += '<div style="font-size:11px; color:#881337; font-weight:800; margin-top:2px;">كود الطالب: ' + r.student_code + '</div>';
                html += '</div>';
                html += '<div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">';
                html += '<span style="background:' + badgeBg + '; color:' + badgeColor + '; padding:2px 8px; border-radius:6px; font-size:10.5px; font-weight:800;">' + r.status_label + '</span>';
                html += '<span style="background:' + vbadgeBg + '; color:' + vbadgeColor + '; padding:2px 8px; border-radius:6px; font-size:10.5px; font-weight:800;">' + r.verification_label + '</span>';
                html += '</div></div>';

                html += '<div style="font-size:11px; color:#475569; line-height:1.5; margin-bottom:4px;">';
                html += '<div><strong>الصف:</strong> ' + r.class_name + ' (' + r.section + ') | <strong>المرجع:</strong> <span style="font-family:monospace;">' + r.reference_no + '</span></div>';
                html += '<div><strong>ولي الأمر:</strong> ' + r.parent_name + ' (' + r.parent_phone + ')</div>';
                html += '</div>';
                html += '</div>';

                // Standardized Compact Action Buttons Row
                html += '<div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:6px; border-top:1px solid #e2e8f0; padding-top:10px; margin-top: auto;">';

                // Button 1: View / Review
                html += '<button type="button" class="eess-btn-action-compact" onclick="wViewCardRequestDetails(' + r.id + ')" style="background:#0f172a; color:white;">';
                html += '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                html += '<span>عرض/مراجعة</span></button>';

                // Button 2: WhatsApp Verification
                if (r.wa_phone) {
                    html += '<a href="' + waUrl + '" target="_blank" class="eess-btn-action-compact" style="background:#25D366; color:white;">';
                    html += '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.3 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>';
                    html += '<span>تحقق واتساب</span></a>';
                } else {
                    html += '<span class="eess-btn-action-compact" style="background:#cbd5e1; color:#64748b;">لا يوجد هاتف</span>';
                }

                // Button 3: Print Official Request Doc
                html += '<button type="button" class="eess-btn-action-compact" onclick="wPrintExitRequestDoc(' + r.id + ')" style="background:#0284c7; color:white;">';
                html += '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';
                html += '<span>طباعة الوثيقة</span></button>';

                // Button 4: Print Student Exit Card (Using Student Affairs Print Engine)
                html += '<button type="button" class="eess-btn-action-compact" onclick="wPrintStudentCard(' + r.student_id + ')" style="background:#881337; color:white;">';
                html += '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>';
                html += '<span>طباعة البطاقة</span></button>';

                // Button 5: Delete Request
                html += '<button type="button" class="eess-btn-action-compact" onclick="wPromptDeleteRequest(' + r.id + ', \'' + r.student_name.replace(/'/g, "\\'") + '\')" style="background:#fee2e2; color:#991b1b; border:1px solid #fecdd3; grid-column: span 2;">';
                html += '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
                html += '<span>حذف طلب التصريح</span></button>';

                html += '</div>';

                html += '</div>';
            });
            box.innerHTML = html;
        } else {
            box.innerHTML = '<div style="text-align:center; color:#64748b; padding:20px; font-size:12.5px; grid-column: span 2;">لا توجد طلبات تصاريح خروج مسجلة حتى الآن.</div>';
        }
    });
}

function wChangeVerificationStatus(reqId, vstatus) {
    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_manage_card_requests',
        action_type: 'update_verification_status',
        request_id: reqId,
        verification_status: vstatus,
        nonce: '<?php echo $admin_nonce; ?>'
    }, function(res) {
        if (res.success) {
            eessShowToast('تم تحديث حالة تحقق ولي الأمر بنجاح.', 'success');
            wLoadCardRequestsFull();
        } else {
            eessShowToast('فشل تحديث حالة التحقق: ' + (res.data || 'خطأ'), 'error');
        }
    });
}

function wPromptDeleteRequest(reqId, studentName) {
    wPendingDeleteReqId = reqId;
    document.getElementById('eess_del_student_name').innerText = studentName;
    document.getElementById('eess-delete-confirm-modal').style.display = 'flex';
}

function wCloseDeleteModal() {
    wPendingDeleteReqId = null;
    document.getElementById('eess-delete-confirm-modal').style.display = 'none';
}

function wExecuteConfirmDelete() {
    if (!wPendingDeleteReqId) return;

    const btn = document.getElementById('eess_btn_confirm_delete');
    btn.disabled = true;
    btn.innerText = 'جاري الحذف...';

    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_manage_card_requests',
        action_type: 'delete',
        request_id: wPendingDeleteReqId,
        nonce: '<?php echo $admin_nonce; ?>'
    }, function(res) {
        btn.disabled = false;
        btn.innerText = 'تأكيد حذف الطلب';
        wCloseDeleteModal();

        if (res.success) {
            eessShowToast(res.data.message || 'تم حذف طلب تصريح الخروج بنجاح مع الحفاظ على سجل الطالب.', 'success');
            wLoadCardRequestsFull();
        } else {
            eessShowToast('فشل حذف الطلب: ' + (res.data || 'خطأ'), 'error');
        }
    });
}

function wViewCardRequestDetails(reqId) {
    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_get_exit_card_request_details',
        request_id: reqId,
        nonce: '<?php echo $admin_nonce; ?>'
    }, function(res) {
        if (res.success && res.data) {
            const d = res.data;
            let html = '<div style="line-height:1.7;">';
            html += '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; background:#f8fafc; padding:12px; border-radius:10px; margin-bottom:12px;">';
            html += '<div><strong>الرقم المرجعي:</strong> <span style="font-family:monospace; font-weight:900; color:#881337;">' + d.reference_no + '</span></div>';
            html += '<div><strong>تاريخ الطلب:</strong> ' + d.created_at + '</div>';
            html += '<div><strong>اسم الطالب:</strong> ' + d.student_name + '</div>';
            html += '<div><strong>كود الطالب:</strong> <span style="font-family:monospace; font-weight:900;">' + d.student_code + '</span></div>';
            html += '<div><strong>الصف والشعبة:</strong> ' + d.class_name + ' (' + d.section + ')</div>';
            html += '<div><strong>الهوية الوطنية:</strong> ' + d.national_id + '</div>';
            html += '<div style="grid-column: span 2;"><strong>ولي الأمر:</strong> ' + d.parent_name + ' (' + d.parent_phone + ')</div>';
            html += '</div>';

            <?php if ($is_admin): ?>
                html += '<div style="margin-bottom:14px; background:#fffbe3; border:1px solid #fde047; padding:10px 14px; border-radius:10px;">';
                html += '<label style="font-size:12px; font-weight:800; color:#854d0e; display:block; margin-bottom:4px;">تعديل حالة الطلب مباشرة:</label>';
                html += '<select onchange="wUpdateReqStatus(' + d.id + ', this.value)" style="width:100%; height:36px; border-radius:8px; border:1px solid #cbd5e1; font-size:12px; font-weight:800;">';
                html += '<option value="submitted" ' + (d.status==='submitted'?'selected':'') + '>تم تقديم الطلب</option>';
                html += '<option value="under_review" ' + (d.status==='under_review'?'selected':'') + '>قيد المراجعة والتدقيق</option>';
                html += '<option value="approved" ' + (d.status==='approved'?'selected':'') + '>موافقة إدارية رسمية</option>';
                html += '<option value="preparing" ' + (d.status==='preparing'?'selected':'') + '>جاري تجهيز وتغليف البطاقة</option>';
                html += '<option value="issued" ' + (d.status==='issued'?'selected':'') + '>تم الإصدار والتسليم</option>';
                html += '<option value="rejected" ' + (d.status==='rejected'?'selected':'') + '>رفض الطلب</option>';
                html += '</select></div>';
            <?php endif; ?>

            if (d.signature_data) {
                html += '<div style="margin-bottom:14px;">';
                html += '<div style="font-weight:800; margin-bottom:4px; color:#0f172a;">التوقيع الإلكتروني المعتمد لولي الأمر:</div>';
                html += '<div style="background:white; border:2px dashed #cbd5e1; border-radius:10px; padding:8px; text-align:center;">';
                html += '<img src="' + d.signature_data + '" style="max-height:60px; object-fit:contain;" alt="Signature">';
                html += '</div></div>';
            }

            html += '<div style="margin-top:14px; border-top:1px solid #e2e8f0; padding-top:10px; display:flex; gap:8px; justify-content:flex-end;">';
            html += '<button type="button" onclick="wPrintExitRequestDoc(' + d.id + ')" style="background:#0284c7; color:white; border:none; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer;">🖨️ طباعة الوثيقة A4</button>';
            html += '<button type="button" onclick="wPrintStudentCard(' + d.student_id + ')" style="background:#881337; color:white; border:none; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer;">🪪 طباعة البطاقة</button>';
            html += '</div>';

            html += '</div>';

            document.getElementById('modal_req_body').innerHTML = html;
            document.getElementById('w-req-view-modal').style.display = 'flex';
        } else {
            eessShowToast('فشل جلب تفاصيل الطلب.', 'error');
        }
    });
}

function wUpdateReqStatus(reqId, status) {
    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_manage_card_requests',
        action_type: 'update_status',
        request_id: reqId,
        status: status,
        nonce: '<?php echo $admin_nonce; ?>'
    }, function(res) {
        if (res.success) {
            eessShowToast('تم تحديث حالة الطلب بنجاح.', 'success');
            document.getElementById('w-req-view-modal').style.display = 'none';
            wLoadCardRequestsFull();
        }
    });
}

function wPrintStudentCard(studentId) {
    window.open('<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=student_card&student_id='); ?>' + studentId, '_blank');
}

function wPrintExitRequestDoc(reqId) {
    window.open('<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=exit_permit_request&request_id='); ?>' + reqId + '&auto_print=1', '_blank');
}
</script>
