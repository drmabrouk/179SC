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
    'required_fields' => array('guardian_phone', 'dob'),
    'max_requests' => 3,
    'redirect_discipline' => 'yes'
));

$portal_mode = $card_settings['portal_mode'] ?? 'card_application';
$required_fields = (array) ($card_settings['required_fields'] ?? array('guardian_phone', 'dob'));
?>

<!-- Outer Flexible Layout Wrapper (Main Wizard + Sidebar) -->
<div class="eess-card-portal-wrapper" style="max-width: 1100px; margin: 20px auto; display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; font-family: 'Cairo', sans-serif; direction: rtl; box-sizing: border-box; color: #0f172a;">

    <!-- MAIN CARD WIZARD CONTAINER (Slightly Wider) -->
    <div class="eess-card-wizard-app" style="flex: 1 1 620px; min-width: 320px; background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(15,23,42,0.08); padding: 24px; box-sizing: border-box;">

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
                <label style="font-size: 13px; font-weight: 800; color: #0f172a; display: block; margin-bottom: 6px;">أدخل اسم الطالب المسجل بالمدرسة للبحث:</label>
                <input type="text" id="w_student_name_input" onkeyup="wDebounceSearchName()" placeholder="ابحث باسم الطالب..." style="width: 100%; height: 44px; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 0 14px; font-size: 13px; font-weight: 700; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
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
                <p style="margin: 0 0 14px 0; font-size: 12px; color: #64748b; font-weight: 600;">يرجى إدخال كود الطالب المسجل أو رقم الهوية الوطنية للتحقق والأمان:</p>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">كود الطالب أو رقم الهوية الوطنية <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="w_verify_code_input" placeholder="أدخل كود الطالب أو الهوية الوطنية..." style="width: 100%; height: 44px; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 0 14px; font-size: 13px; font-weight: 700; box-sizing: border-box;">
                </div>

                <button type="button" onclick="wVerifyStudentIdentity()" id="w_btn_verify_id" style="width: 100%; height: 42px; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">تأكيد والتحقق من الهوية</button>
            </div>

            <!-- Verification Result & Existing Status Panel -->
            <div id="w-verified-status-panel" style="display: none; margin-bottom: 18px;"></div>

            <!-- DYNAMIC MISSING REQUIRED DATA COMPLETION FORM -->
            <div id="w-missing-data-container" style="display: none; background: #fffbe3; border: 1.5px solid #fde047; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                <div style="font-size: 13.5px; font-weight: 900; color: #854d0e; margin-bottom: 8px;">
                    ⚠️ يتطلب النظام استكمال البيانات المطلوبة التالية للطالب قبل المتابعة:
                </div>

                <form id="w_missing_data_form" onsubmit="wSubmitMissingData(event)">
                    <!-- Dynamic fields populated via JS based on missing_fields -->
                    <div id="w_missing_fields_render_box"></div>

                    <div style="margin-top: 14px; text-align: left;">
                        <button type="submit" id="w_btn_save_missing" style="height: 42px; padding: 0 24px; background: #854d0e; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">حفظ وتحديث البيانات الحالية ✓</button>
                    </div>
                </form>
            </div>

            <!-- Mandatory Official Student Photo Upload Box (If Profile Photo Missing for Card Mode) -->
            <div id="w-photo-upload-container" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 13.5px; color: #166534; margin-bottom: 8px;">
                    <span>📷 رفع صورة شخصية رسمية معتمدة للطالب</span>
                </div>
                <p style="margin: 0 0 12px 0; font-size: 12px; color: #14532d; line-height: 1.5;">
                    لإتمام طلب تصريح الخروج، يرجى رفع صورة شخصية رسمية خلفية بيضاء.
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
                    أقر أنا ولي أمر الطالب المذكور أعلاه بطلبي الرسمي لإصدار بطاقة تصريح الخروج الرقمية للطالب. وأتحمل المسؤولية الكاملة عن خروج الطالب واستئذانه بموجب هذا التصريح.
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

    <!-- SEPARATE RIGHT SIDEBAR CONTAINER (Settings, Required Data, Card Requests) -->
    <div class="eess-card-portal-sidebar" style="flex: 0 0 320px; width: 320px; max-width: 100%; background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(15,23,42,0.08); padding: 20px; box-sizing: border-box;">

        <?php if ($is_admin): ?>
            <!-- ADMIN CONTROLS: PORTAL SETTINGS -->
            <div style="margin-bottom: 22px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
                <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>⚙️ إعدادات البوابة</span>
                </h3>

                <form id="eess_card_settings_form" onsubmit="wSavePortalSettings(event)">
                    <!-- Portal Application Mode Control -->
                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 12px; font-weight: 800; color: #334155; display: block; margin-bottom: 6px;">نمط عمل البوابة (Portal Mode):</label>
                        <select name="portal_mode" id="sb_portal_mode" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 12px; font-weight: 700; padding: 0 10px;">
                            <option value="card_application" <?php selected($portal_mode, 'card_application'); ?>>تقديم بطاقات تصريح الخروج</option>
                            <option value="update_only" <?php selected($portal_mode, 'update_only'); ?>>تحديث بيانات الطلاب فقط</option>
                        </select>
                    </div>

                    <!-- Required Data for Update Checkboxes -->
                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 12px; font-weight: 800; color: #334155; display: block; margin-bottom: 6px;">البيانات المطلوبة للتحديث:</label>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 11.5px; font-weight: 700; color: #475569; max-height: 180px; overflow-y: auto; padding: 8px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <?php
                            $available_fields = array(
                                'guardian_phone' => 'رقم هاتف ولي الأمر (+971)',
                                'dob' => 'تاريخ الميلاد',
                                'gender' => 'الجنس',
                                'guardian_name' => 'اسم ولي الأمر',
                                'emirate' => 'إمارة السكن',
                                'address' => 'العنوان التفصيلي',
                                'nationality' => 'الجنسية',
                                'national_id' => 'رقم الهوية الوطنية'
                            );
                            foreach ($available_fields as $fk => $flabel):
                                $chk = in_array($fk, $required_fields) ? 'checked' : '';
                            ?>
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="required_fields[]" value="<?php echo esc_attr($fk); ?>" <?php echo $chk; ?>>
                                    <span><?php echo esc_html($flabel); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" style="width: 100%; height: 36px; background: #0f172a; color: white; border: none; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer;">حفظ إعدادات البوابة ✓</button>
                </form>
            </div>
        <?php else: ?>
            <!-- PUBLIC VISITOR SIDEBAR INFO -->
            <div style="margin-bottom: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
                <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 900; color: #0f172a;">بوابة الخدمات الذاتية</h4>
                <div style="font-size: 11.5px; color: #64748b; line-height: 1.6;">
                    تتيح لك البوابة تحديث بيانات الطالب المسجل أو إرسال طلب تصريح خروج رسمي إلكترونياً.
                </div>
            </div>
        <?php endif; ?>

        <!-- CARD REQUESTS MANAGEMENT SECTION -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; color: #0f172a;">📋 طلبات التصاريح</h3>
                <?php if ($is_admin): ?>
                    <button type="button" onclick="wLoadCardRequests()" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer;">تحديث ↺</button>
                <?php endif; ?>
            </div>

            <!-- Visual Request Cards List -->
            <div id="sb-card-requests-list" style="display: flex; flex-direction: column; gap: 10px; max-height: 480px; overflow-y: auto;">
                <div style="text-align: center; font-size: 11.5px; color: #64748b; padding: 12px;">جاري تحميل الطلبات...</div>
            </div>
        </div>

    </div>

</div>

<!-- VIEW REQUEST MODAL -->
<div id="w-req-view-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 15px; box-sizing: border-box;">
    <div style="background: white; border-radius: 16px; max-width: 500px; width: 100%; padding: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); direction: rtl; font-family: 'Cairo', sans-serif;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 14px;">
            <h4 style="margin: 0; font-size: 15px; font-weight: 900; color: #0f172a;" id="modal_req_title">تفاصيل طلب تصريح الخروج</h4>
            <button type="button" onclick="document.getElementById('w-req-view-modal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">✕</button>
        </div>
        <div id="modal_req_body" style="font-size: 12px; color: #334155; line-height: 1.6;"></div>
    </div>
</div>

<!-- HIDDEN PRINT CONTAINER -->
<div id="eess-card-print-frame" style="display: none;"></div>

<script>
let wCurrentStep = 1;
let wSelectedStudent = null;
let wVerifiedData = null;
let wSearchTimeout = null;
let wSubmitting = false;

// Signature pad
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

    wLoadCardRequests();
});

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

    if (val.length < 1) {
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
            suggestions.innerHTML = '<div style="background:#fef2f2; border:1px solid #fecdd3; border-radius:10px; padding:12px; color:#991b1b; font-size:12px; font-weight:700; text-align:center;">' + (res.data || 'لم يتم العثور على طالب يطابق الاسم المدخل.') + '</div>';
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
        alertBox.style.display = 'block';
        alertBox.style.background = '#fef2f2';
        alertBox.style.color = '#991b1b';
        alertBox.innerText = 'يرجى إدخال كود الطالب أو الهوية الوطنية.';
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
            html += '<div style="font-size:13px; font-weight:800; color:#16a34a; margin-bottom:6px;">✓ تم التحقق بنجاح من هويّة الطالب</div>';

            // Check dynamic missing fields
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
            alertBox.style.display = 'block';
            alertBox.style.background = '#fef2f2';
            alertBox.style.color = '#991b1b';
            alertBox.innerText = res.data || 'رمز التحقق غير مطابق للبيانات المسجلة.';
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
            btn.innerText = 'حفظ وتحديث البيانات الحالية ✓';

            if (res.success) {
                alert(res.data.message || 'تم حغظ البيانات بنجاح.');
                document.getElementById('w-missing-data-container').style.display = 'none';

                // Re-trigger verify to update UI cleanly
                wVerifyStudentIdentity();
            } else {
                alert('خطأ: ' + (res.data || 'فشل حفظ البيانات.'));
            }
        },
        error: function() {
            btn.disabled = false;
            btn.innerText = 'حفظ وتحديث البيانات الحالية ✓';
            alert('حدث خطأ في الاتصال بالسيرفر.');
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
                wLoadCardRequests();
            } else {
                btn.disabled = false;
                btn.innerHTML = 'تأكيد وإرسال الطلب النهائي ✓';
                alert('حدث خطأ أثناء إرسال الطلب: ' + (res.data || 'فشل الحفظ'));
            }
        },
        error: function() {
            wSubmitting = false;
            btn.disabled = false;
            btn.innerHTML = 'تأكيد وإرسال الطلب النهائي ✓';
            alert('حدث خطأ في الاتصال بالسيرفر أثناء إرسال الطلب.');
        }
    });
}

function wSavePortalSettings(e) {
    e.preventDefault();
    const form = document.getElementById('eess_card_settings_form');
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
                alert(res.data.message || 'تم حفظ الإعدادات بنجاح.');
                location.reload();
            } else {
                alert('خطأ: ' + (res.data || 'فشل الحفظ'));
            }
        }
    });
}

function wLoadCardRequests() {
    const box = document.getElementById('sb-card-requests-list');
    if (!box) return;

    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_manage_card_requests',
        action_type: 'list',
        nonce: '<?php echo $admin_nonce; ?>'
    }, function(res) {
        if (res.success && res.data && res.data.length > 0) {
            let html = '';
            res.data.forEach(r => {
                let badgeBg = '#fef08a';
                let badgeColor = '#854d0e';
                if (r.status === 'approved') { badgeBg = '#dcfce7'; badgeColor = '#166534'; }
                else if (r.status === 'rejected') { badgeBg = '#fee2e2'; badgeColor = '#991b1b'; }

                html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">';
                html += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">';
                html += '<div style="font-size:12.5px; font-weight:900; color:#0f172a;">' + r.student_name + '</div>';
                html += '<span style="background:' + badgeBg + '; color:' + badgeColor + '; padding:2px 8px; border-radius:6px; font-size:10px; font-weight:800;">' + r.status_label + '</span>';
                html += '</div>';

                html += '<div style="font-size:11px; color:#64748b; line-height:1.5; margin-bottom:8px;">';
                html += '<strong>الصف:</strong> ' + r.class_name + ' (' + r.section + ') | <strong>كود:</strong> ' + r.student_code + '<br>';
                html += '<strong>مرجع:</strong> <span style="color:#881337; font-weight:800;">' + r.reference_no + '</span>';
                html += '</div>';

                html += '<div style="display:flex; gap:6px; flex-wrap:wrap; font-size:10.5px;">';
                html += '<button type="button" onclick="wViewCardRequestDetails(' + r.id + ')" style="background:#0f172a; color:white; border:none; padding:4px 8px; border-radius:6px; font-weight:800; cursor:pointer;">عرض الطلب</button>';
                html += '<button type="button" onclick="wPrintStudentCard(' + r.student_id + ')" style="background:#881337; color:white; border:none; padding:4px 8px; border-radius:6px; font-weight:800; cursor:pointer;">طباعة البطاقة</button>';
                html += '</div>';

                html += '</div>';
            });
            box.innerHTML = html;
        } else {
            box.innerHTML = '<div style="text-align:center; font-size:11.5px; color:#64748b; padding:12px;">لا توجد طلبات مسجلة حتى الآن.</div>';
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
            html += '<div><strong>الرقم المرجعي:</strong> ' + d.reference_no + '</div>';
            html += '<div><strong>اسم الطالب:</strong> ' + d.student_name + ' (' + d.class_name + ' / ' + d.section + ')</div>';
            html += '<div><strong>كود الطالب:</strong> ' + d.student_code + '</div>';
            html += '<div><strong>ولي الأمر:</strong> ' + d.parent_name + ' (' + d.parent_phone + ')</div>';
            html += '<div><strong>تاريخ الطلب:</strong> ' + d.created_at + '</div>';
            html += '<div><strong>حالة الطلب:</strong> ' + d.status_label + '</div>';

            if (d.signature_data) {
                html += '<div style="margin-top:10px;"><strong>التوقيع الإلكتروني:</strong><br><img src="' + d.signature_data + '" style="max-height:60px; border:1px solid #cbd5e1; border-radius:6px; padding:4px; margin-top:4px;"></div>';
            }

            <?php if ($is_admin): ?>
                html += '<div style="margin-top:14px; border-top:1px solid #e2e8f0; padding-top:10px; display:flex; gap:8px;">';
                html += '<button type="button" onclick="wUpdateReqStatus(' + d.id + ', \'approved\')" style="background:#16a34a; color:white; border:none; padding:6px 14px; border-radius:6px; font-weight:800; cursor:pointer;">موافقة</button>';
                html += '<button type="button" onclick="wUpdateReqStatus(' + d.id + ', \'rejected\')" style="background:#dc2626; color:white; border:none; padding:6px 14px; border-radius:6px; font-weight:800; cursor:pointer;">رفض</button>';
                html += '</div>';
            <?php endif; ?>

            html += '</div>';

            document.getElementById('modal_req_body').innerHTML = html;
            document.getElementById('w-req-view-modal').style.display = 'flex';
        } else {
            alert('فشل جلب تفاصيل الطلب.');
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
            alert('تم تحديث حالة الطلب بنجاح.');
            document.getElementById('w-req-view-modal').style.display = 'none';
            wLoadCardRequests();
        }
    });
}

function wPrintStudentCard(studentId) {
    window.open('<?php echo admin_url('admin-ajax.php?action=sm_print_student_card&student_id='); ?>' + studentId, '_blank');
}
</script>
