<?php

namespace App\Support;

/**
 * The starting text for the Terms of Service and Privacy Policy pages,
 * written for how Trenakt actually works (activation fee, wallet funding,
 * task rewards, reward revocation, referral program, Nigeria-based
 * payouts). Used only as a fallback default - see Admin\LegalPages\Index
 * and routes/web.php - the moment a super admin saves anything on the
 * Legal Pages screen, the saved Setting values take over completely and
 * this class is never read again for that page. Plain text, not HTML: see
 * LegalContentRenderer for why.
 */
class DefaultLegalContent
{
    public static function terms(): string
    {
        return <<<'TEXT'
Last updated: not yet published

These terms govern your use of Trenakt. By creating an account, you agree to them. If you don't agree, don't use the platform.

1. What Trenakt is

Trenakt connects businesses that need small tasks completed, such as surveys, testing, or research, with participants who complete those tasks in exchange for payment. Businesses post campaigns and fund them through their wallet. Participants browse available tasks, submit completed work, and earn money once it's approved.

2. Who can use Trenakt

You must be at least 18 years old to register. You're responsible for providing accurate information when you sign up, including your name, email, and country. If any of your details change, update them in your profile.

3. Accounts

You're responsible for keeping your login details secure and for everything that happens under your account. Tell us right away if you think someone else has access to it. We may suspend or close an account that provides false information, is used to abuse the platform, or violates these terms.

4. Participant terms

As a participant, you agree to complete tasks honestly and to the standard the campaign describes. Submitting false, copied, or low-effort work to get paid is not allowed and can result in your submission being rejected, or your reward being revoked even after payment if we discover it afterward. Some campaigns may require an activation fee before you can start accepting tasks. This fee, when active, is disclosed clearly before you pay it.

Rewards for approved tasks are added to your earnings balance. Once you're eligible to withdraw, funds are paid out to your bank account on the schedule shown in the app. We reserve the right to reverse a reward that was paid in error, through fraud, or in violation of these terms. This does not reopen the task for resubmission, it only removes the reward.

5. Referral program

If referrals are active, you may earn a reward for participants who join using your referral link or code and go on to activate their account. Referral rewards are only paid for genuine, independent sign-ups. Creating fake accounts or referring yourself to earn rewards is not allowed, and any rewards earned this way will be reversed.

6. Business terms

As a business, you agree to fund your wallet before launching a campaign and to only post campaigns for lawful, genuine work. We review campaigns before they go live and may reject or remove any campaign that is misleading, illegal, or against these terms. Funds added to your wallet are used to pay participants for approved submissions. Wallet funding is processed through our payment partners and is subject to their own terms as well.

7. Payments

All payments on Trenakt, whether wallet funding by a business or the activation fee paid by a participant, are processed by third-party payment providers. We do not store your full card or bank details ourselves. Payouts to participants are only available in countries we currently support, and this may change over time.

8. Prohibited conduct

You may not use Trenakt to submit fraudulent task responses, create multiple accounts to abuse referral or task rewards, attempt to access another user's account, interfere with the normal operation of the platform, or use it for anything unlawful.

9. Suspension and termination

We may suspend or terminate an account that violates these terms. If your account is closed for a policy violation, any pending or unwithdrawn balance may be withheld pending review.

10. Limitation of liability

Trenakt is provided as is. We aren't liable for losses arising from your use of the platform beyond what is required by law, including delays or failures caused by third-party payment providers.

11. Changes to these terms

We may update these terms from time to time. Continuing to use Trenakt after a change means you accept the updated terms.

12. Governing law

These terms are governed by the laws of the Federal Republic of Nigeria.

13. Contact

Questions about these terms can be sent to support@trenakt.com.
TEXT;
    }

    public static function privacy(): string
    {
        return <<<'TEXT'
Last updated: not yet published

This policy explains what information Trenakt collects and how we use it.

1. Information we collect

When you register, we collect your name, email address, and country. Depending on how you use the platform, we may also collect your phone number, date of birth, and, if you're a participant looking to withdraw earnings, your bank account details. We also keep a record of task submissions, campaign activity, and wallet transactions tied to your account.

2. How we use your information

We use your information to run your account, process task submissions and rewards, process wallet funding and withdrawals, send you account and transaction related emails, and detect fraud or misuse of the platform.

3. Payment information

We don't store your full card details. Wallet funding and the activation fee are handled directly by our payment partners, Paystack and Flutterwave, who process that information under their own security standards. We only receive confirmation of whether a payment succeeded and the amount.

4. Location

When you register, we make a best-effort attempt to detect your country automatically to speed up sign-up. This uses your IP address through a third-party geolocation service. You can always correct the detected country yourself, and this detection is never used for anything beyond pre-filling that one field.

5. Email communication

We send emails for account verification, task approvals and rejections, wallet activity, withdrawal updates, and other account related events. These are necessary to run the service and are not marketing emails.

6. Data sharing

We don't sell your personal information. We share what's necessary with our payment processors to complete transactions, and with email delivery services to send account related messages. We may also disclose information if required by law.

7. Data retention

We keep your account information for as long as your account is active. If you close your account, we retain transaction records for as long as legally required for financial recordkeeping, and remove other personal information within a reasonable time.

8. Your rights

You can review and update most of your information from your profile. To request a copy of your data or ask us to delete it, contact us at the address below. Some information, like financial transaction history, may need to be retained even after a deletion request for legal or accounting reasons.

9. Security

We take reasonable steps to protect your information, including encrypting passwords and sensitive settings. No system is completely secure, so we can't guarantee absolute protection, but we work to keep your data safe.

10. Children's privacy

Trenakt is not intended for anyone under 18. We don't knowingly collect information from minors.

11. Changes to this policy

We may update this policy from time to time. Material changes will be reflected here with an updated date.

12. Contact

Questions about this policy can be sent to support@trenakt.com.
TEXT;
    }
}
