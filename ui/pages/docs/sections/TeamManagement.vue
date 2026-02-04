<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const inviteMemberSteps = [
    'Go to your team settings (click your profile, then "Team Settings").',
    'Navigate to the "Members" or "Invite" section.',
    'Enter the email address of the person you want to invite.',
    'Select their role (member or administrator).',
    'Click "Send Invitation".',
    'The invitee will receive an email with instructions to join.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Team Management</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Teams allow multiple users to collaborate on managing church and schedule data. Each team
      typically represents a diocese or group of parishes working together.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Understanding Teams
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Key concepts about teams:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li>
        <strong>Data Isolation</strong> - Each team's data is separate. You can only see and edit
        data belonging to your current team.
      </li>
      <li>
        <strong>Multiple Teams</strong> - Users can be members of multiple teams and switch between
        them using the team selector in the top bar.
      </li>
      <li>
        <strong>Roles</strong> - Team members can have different roles with different permissions.
      </li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Inviting Team Members
    </h3>

    <DocSteps :steps="inviteMemberSteps" title="To invite someone to your team:" />

    <DocTip>
      Invitations expire after a certain period. If someone doesn't receive their invitation, check
      their spam folder or send a new invitation.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Team Roles</h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <div class="tw:space-y-3">
        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Owner</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Full control over the team. Can manage all settings, invite/remove members, and delete
            the team.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Administrator</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Can manage team data and invite new members. Cannot delete the team or remove the owner.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Member</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Can view and edit data but cannot manage team settings or invite new members.
          </p>
        </div>
      </div>
    </v-card>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Switching Teams</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      If you belong to multiple teams, use the team selector dropdown in the top navigation bar to
      switch between them. When you switch teams, you'll see only that team's data.
    </p>

    <DocWarning>
      Removing a team member is immediate. They will lose access to all team data. Make sure to
      transfer any responsibilities before removing someone.
    </DocWarning>

    <DocRelated :sections="['getting-started', 'data-management']" />
  </div>
</template>
