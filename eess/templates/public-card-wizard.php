<?php
/**
 * Template Name: Public Exit Card Request Wizard
 * Shortcode: [card]
 */

if (!defined('ABSPATH')) exit;

$school_info = SM_Settings::get_school_info();
$sys_logo = !empty($school_info['school_logo']) ? $school_info['school_logo'] : (!empty($school_info['logo_url']) ? $school_info['logo_url'] : SM_PLUGIN_URL . 'assets/images/logo.png');
$school_name = 'مؤسسة الشعلة للتعليم والتطوير';
$ajax_url = admin_url('admin-ajax.php');
?>

<div class="eess-card-wizard-app" style="max-width: 680px; margin: 20px auto; background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(15,23,42,0.08); font-family: 'Cairo', sans-serif; direction: rtl; padding: 24px; box-sizing: border-box; color: #0f172a;">

    <!-- Header & Branding Banner -->
    <div style="text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 20px;">
        <div style="width: 72px; height: 72px; margin: 0 auto 10px auto; background: #ffffff; border-radius: 16px; padding: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center;">
            <img src="<?php echo esc_url($sys_logo); ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 12px;" alt="Logo">
        </div>
        <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 900; color: #0f172a;"><?php echo esc_html($school_name); ?></h2>
        <div style="font-size: 13px; color: #881337; font-weight: 800;">بوابة تقديم ومتابعة طلبات بطاقات تصريح الخروج الرقمية</div>
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
            <div style="font-size: 11px; font-weight: 800; color: #64748b;">التحقق من البيانات</div>
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
            <label style="font-size: 13px; font-weight: 800; color: #0f172a; display: block; margin-bottom: 6px;">ادخل بداية اسم الطالب المسجل بالمدرسة (10 حروف على الأقل):</label>
            <input type="text" id="w_student_name_input" onkeyup="wDebounceSearchName()" placeholder="مثال: عبد الله محمد علي..." style="width: 100%; height: 44px; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 0 14px; font-size: 13px; font-weight: 700; box-sizing: border-box; outline: none; transition: border-color 0.2s;">
            <div style="font-size: 11px; color: #64748b; margin-top: 6px; font-weight: 600;">تنبيه أمني: يتطلب البحث كتابة 10 حروف من بداية الاسم المسجل للنظام لحماية الخصوصية.</div>
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

    <!-- STEP 2: VERIFY STUDENT IDENTITY -->
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

        <!-- Mandatory Official Student Photo Upload Box (If Profile Photo Missing) -->
        <div id="w-photo-upload-container" style="display: none; background: #fffbe3; border: 1.5px solid #fde047; border-radius: 14px; padding: 18px; margin-bottom: 18px;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 13.5px; color: #854d0e; margin-bottom: 8px;">
                <span class="dashicons dashicons-camera" style="font-size: 20px;"></span>
                <span>تنبيه هائم: يتطلب النظام رفع صورة شخصية رسمية معتمدة للطالب</span>
            </div>
            <p style="margin: 0 0 12px 0; font-size: 12px; color: #713f12; line-height: 1.5;">
                الملف الشخصي للطالب لا يحتوي على صورة رسمية معتمدة بالنظام. لإتمام طلب تصريح الخروج، يرجى رفع صورة شخصية رسمية مستوفية للشروط التالية:
            </p>
            <ul style="margin: 0 0 12px 0; padding-right: 20px; font-size: 11.5px; color: #854d0e; line-height: 1.6; font-weight: 700;">
                <li>خلفية بيضاء ناصعة وموحدة بدون مؤثرات.</li>
                <li>مظهر رسمي (صورة جواز السفر / الهوية الوطنية).</li>
                <li>صورة حديثة لالتقاطها مدة لا تتجاوز سنة واحدة، بوضوح وجلاء ملامح الوجه.</li>
                <li>صيغة الملف (JPG, PNG, WEBP) وبحجم لا يتجاوز 5 ميجابايت.</li>
            </ul>

            <div style="margin-bottom: 10px;">
                <input type="file" id="w_student_photo_file" accept="image/jpeg,image/png,image/webp" onchange="wValidateStudentPhoto(this)" style="width: 100%; font-size: 12px; background: white; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>

            <div id="w_photo_preview_box" style="display: none; margin-top: 10px; background: white; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: center;">
                <img id="w_photo_preview_img" src="" style="width: 90px; height: 110px; object-fit: cover; border-radius: 8px; border: 2px solid #0f172a; margin-bottom: 6px;" alt="Student Photo Preview">
                <div style="font-size: 11px; color: #16a34a; font-weight: 800;" id="w_photo_status_msg">✓ تم التحقق من توافق الصورة المرفقة.</div>
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
                <input type="tel" id="w_parent_phone" placeholder="050XXXXXXX" style="width: 100%; height: 42px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 12.5px; box-sizing: border-box;">
            </div>

            <!-- Official Declaration Text Box -->
            <div style="background: #fffbe3; border: 1px solid #fde047; border-radius: 10px; padding: 14px; font-size: 11.5px; color: #854d0e; line-height: 1.6; margin-bottom: 14px;">
                <strong>تعهد وإقرار ولي الأمر الرسمي:</strong><br>
                أقر أنا ولي أمر الطالب المذكور أعلاه بطلبي الرسمي لإصدار بطاقة تصريح الخروج الرقمية للطالب. وأتحمل المسؤولية الكاملة عن خروج الطالب واستئذانه بموجب هذا التصريح عقب اعتماده وموافقته من قبل إدارة المدرسة الموقرة. وأعلم أن تقديم الطلب لا يعتبر موافقة نهائية إلا بعد اكتمال المراجعة.
            </div>

            <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">
                <input type="checkbox" id="w_declaration_chk" onchange="wCheckStep3Valid()" style="width: 18px; height: 18px; margin-top: 1px;">
                <span>أقر وأوافق على كافة الشروط والالتزامات الواردة في الإقرار أعلاه *</span>
            </label>

            <!-- HTML5 Electronic Signature Canvas Box -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="font-size: 12px; font-weight: 800; color: #0f172a;">التوقيع الإلكتروني لولي الأمر <span style="color:#ef4444;">*</span></label>
                    <button type="button" onclick="wClearSignature()" style="background: #fee2e2; color: #991b1b; border: none; padding: 2px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer;">مسح التوقيع ↺</button>
                </div>
                <div style="border: 2px dashed #cbd5e1; border-radius: 12px; background: #ffffff; overflow: hidden; touch-action: none;">
                    <canvas id="w-signature-pad" width="600" height="150" style="width: 100%; height: 140px; display: block; cursor: crosshair;"></canvas>
                </div>
                <div style="font-size: 10.5px; color: #64748b; margin-top: 4px;">يرجى التوقيع بإصبعك أو القلم بالصندوق الأبيض أعلاه.</div>
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

            <!-- Signature Preview -->
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
        <div style="width: 64px; height: 64px; background: #dcfce7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #16a34a; margin-bottom: 14px;">
            <span class="dashicons dashicons-yes" style="font-size: 36px; width: 36px; height: 36px;"></span>
        </div>
        <h3 style="margin: 0 0 6px 0; font-size: 20px; font-weight: 900; color: #15803d;">تم تقديم طلب تصريح الخروج بنجاح</h3>
        <p style="font-size: 13px; color: #475569; margin: 0 0 16px 0;">تم تسجيل الطلب بالنظام وهو الآن قيد المراجعة المباشرة من قسم شؤون الطلاب.</p>

        <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px; padding: 16px; max-width: 380px; margin: 0 auto 20px auto;">
            <div style="font-size: 11px; color: #64748b; font-weight: 800; margin-bottom: 2px;">الرقم المرجعي للطلب (Reference No):</div>
            <div style="font-size: 22px; font-weight: 900; color: #881337; font-family: monospace; letter-spacing: 1px;" id="w_success_ref_no">EXT-2026-00000</div>
        </div>

        <div style="font-size: 12px; color: #64748b; line-height: 1.6; max-width: 440px; margin: 0 auto 20px auto;">
            يرجى الاحتفاظ بالرقم المرجعي لمتابعة الحالة. سيقوم فريق المدرسة بالتواصل معكم في حال الحاجة لأي تأكيد إضافي قبل تفعيل وتجهيز البطاقة.
        </div>

        <button type="button" onclick="location.reload()" style="height: 42px; padding: 0 26px; background: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer;">الرجوع للصفحة الرئيسية</button>
    </div>

</div>

<script>
let wCurrentStep = 1;
let wSelectedStudent = null;
let wVerifiedData = null;
let wSearchTimeout = null;
let wSubmitting = false;

// HTML5 Canvas Signature Logic
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
    wSearchTimeout = setTimeout(wSearchStudentName, 350);
}

function wSearchStudentName() {
    const val = document.getElementById('w_student_name_input').value.trim();
    const suggestions = document.getElementById('w-search-suggestions');
    const alertBox = document.getElementById('w-alert-box');
    alertBox.style.display = 'none';

    if (val.length < 10) {
        suggestions.style.display = 'none';
        return;
    }

    suggestions.style.display = 'block';
    suggestions.innerHTML = '<div style="text-align:center; padding:12px; font-weight:700; color:#64748b;">جاري البحث عن تطابقات... ⏳</div>';

    jQuery.post('<?php echo $ajax_url; ?>', {
        action: 'sm_public_search_student',
        name_query: val
    }, function(res) {
        if (res.success && res.data && res.data.length > 0) {
            let html = '<div style="background:#ffffff; border:1px solid #cbd5e1; border-radius:10px; max-height:200px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.05);">';
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

let wPhotoValidated = false;

function wValidateStudentPhoto(input) {
    const previewBox = document.getElementById('w_photo_preview_box');
    const previewImg = document.getElementById('w_photo_preview_img');
    const statusMsg  = document.getElementById('w_photo_status_msg');
    const btnNext    = document.getElementById('w_btn_next_2');

    if (!input.files || !input.files[0]) {
        previewBox.style.display = 'none';
        wPhotoValidated = false;
        if (wVerifiedData && !wVerifiedData.has_photo) {
            btnNext.disabled = true;
            btnNext.style.opacity = '0.5';
        }
        return;
    }

    const file = input.files[0];
    const maxMB = 5;
    const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if (!allowed.includes(file.type)) {
        alert('صيغة الملف غير مسموح بها. يرجى اختيار صورة بصيغة JPG أو PNG أو WEBP.');
        input.value = '';
        previewBox.style.display = 'none';
        wPhotoValidated = false;
        btnNext.disabled = true;
        btnNext.style.opacity = '0.5';
        return;
    }

    if (file.size > maxMB * 1024 * 1024) {
        alert('حجم الملف يتجاوز الحد الأقصى المسموح به (5 ميجابايت).');
        input.value = '';
        previewBox.style.display = 'none';
        wPhotoValidated = false;
        btnNext.disabled = true;
        btnNext.style.opacity = '0.5';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        previewImg.src = e.target.result;
        previewBox.style.display = 'block';
        statusMsg.innerText = '✓ تم اختيار ومعاينة الصورة بنجاح وتأكيد الشروط الرسمية.';
        wPhotoValidated = true;

        if (wVerifiedData && !wVerifiedData.active_request && !wVerifiedData.exceeded_limit) {
            btnNext.disabled = false;
            btnNext.style.opacity = '1';
            btnNext.style.cursor = 'pointer';
        }
    };
    reader.readAsDataURL(file);
}

function wVerifyStudentIdentity() {
    const codeVal = document.getElementById('w_verify_code_input').value.trim();
    const alertBox = document.getElementById('w-alert-box');
    const panel = document.getElementById('w-verified-status-panel');
    const photoContainer = document.getElementById('w-photo-upload-container');

    alertBox.style.display = 'none';
    panel.style.display = 'none';
    photoContainer.style.display = 'none';
    wPhotoValidated = false;

    if (!codeVal) {
        alertBox.style.display = 'block';
        alertBox.style.background = '#fef2f2';
        alertBox.style.color = '#991b1b';
        alertBox.innerText = 'يرجى إدخال كود الطالب أو الهوية الوطنية.';
        return;
    }

    const btn = document.getElementById('w_btn_verify_id');
    btn.disabled = true;
    btn.innerText = 'جاري التحقق... ⏳';

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
            html += '<div style="font-size:13px; font-weight:800; color:#16a34a; margin-bottom:8px;">✓ تم التحقق بنجاح من بيانات الطالب</div>';
            html += '<div style="font-size:12px; color:#475569; line-height:1.6; margin-bottom:12px;">';
            html += '<strong>عدد الطلبات السابقة هذا العام:</strong> ' + res.data.total_prev_requests + ' / ' + res.data.max_allowed + '<br>';

            if (res.data.active_request) {
                const ar = res.data.active_request;
                html += '</div>';
                html += '<div style="background:#fefce8; border:1px solid #fef08a; border-radius:10px; padding:12px; margin-top:8px;">';
                html += '<div style="font-weight:900; color:#854d0e; font-size:13px;">تنبيه: يوجد طلب نشط قائم لهذا الطالب (رقم: ' + ar.reference_no + ')</div>';
                html += '<div style="font-size:12px; color:#a16207; margin-top:4px;"><strong>حالة الطلب الحالية:</strong> ' + ar.status_label + '</div>';
                html += '<div style="font-size:11.5px; color:#713f12; margin-top:2px;">' + ar.status_desc + '</div>';
                html += '</div>';
                btnNext.disabled = true;
                btnNext.style.opacity = '0.5';
            } else if (res.data.exceeded_limit) {
                html += '</div>';
                html += '<div style="background:#fef2f2; border:1px solid #fecdd3; border-radius:10px; padding:12px; color:#991b1b; font-size:12px; font-weight:700;">';
                html += '⚠️ تم تجاوز الحد الأقصى المسموح به لطلبات تصريح الخروج هذا العام الدراسي. يُرجى مراجعة قسم السلوك والانضباط المدرسي لمتابعة الحالة.';
                html += '</div>';
                btnNext.disabled = true;
                btnNext.style.opacity = '0.5';
            } else {
                html += '</div>';

                if (!res.data.has_photo) {
                    photoContainer.style.display = 'block';
                    btnNext.disabled = true;
                    btnNext.style.opacity = '0.5';
                    btnNext.style.cursor = 'not-allowed';
                } else {
                    btnNext.disabled = false;
                    btnNext.style.opacity = '1';
                    btnNext.style.cursor = 'pointer';
                }
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

function wCheckStep3Valid() {
    const name = document.getElementById('w_parent_name').value.trim();
    const phone = document.getElementById('w_parent_phone').value.trim();
    const chk = document.getElementById('w_declaration_chk').checked;
    const isSigned = !wIsCanvasBlank();

    const btn = document.getElementById('w_btn_next_3');
    if (name.length >= 3 && phone.length >= 8 && chk && isSigned) {
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
        document.getElementById('w-panel-step-' + i).style.display = (i === stepNum) ? 'block' : 'none';
        const ind = document.getElementById('w-step-ind-' + i);
        const numBox = ind.querySelector('.w-step-num');
        if (i <= stepNum) {
            numBox.style.background = '#881337';
            numBox.style.color = 'white';
        } else {
            numBox.style.background = '#cbd5e1';
            numBox.style.color = '#475569';
        }
    }

    const linePct = ((stepNum - 1) / 3) * 80;
    document.getElementById('w-progress-line').style.width = linePct + '%';

    if (stepNum === 4) {
        document.getElementById('w_sum_stu_name').innerText = wVerifiedData.student.name;
        document.getElementById('w_sum_stu_class').innerText = wVerifiedData.student.class_name + ' (' + wVerifiedData.student.section + ')';
        document.getElementById('w_sum_stu_code').innerText = wVerifiedData.student.student_code;
        document.getElementById('w_sum_parent_name').innerText = document.getElementById('w_parent_name').value.trim();
        document.getElementById('w_sum_parent_phone').innerText = document.getElementById('w_parent_phone').value.trim();
        if (wCanvas) {
            document.getElementById('w_sum_sig_img').src = wCanvas.toDataURL();
        }
    }

    document.getElementById('w-progress-bar').scrollIntoView({ behavior: 'smooth' });
}

function wSubmitExitCardFinal() {
    if (wSubmitting) return;

    const btn = document.getElementById('w_btn_submit_final');
    wSubmitting = true;
    btn.disabled = true;
    btn.innerHTML = 'جاري إرسال وتوثيق الطلب... ⏳';

    const formData = new FormData();
    formData.append('action', 'sm_public_submit_exit_card');
    formData.append('student_id', wSelectedStudent.id);
    formData.append('verify_code', document.getElementById('w_verify_code_input').value.trim());
    formData.append('parent_name', document.getElementById('w_parent_name').value.trim());
    formData.append('parent_phone', document.getElementById('w_parent_phone').value.trim());
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
</script>
