<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    bio: props.user.bio || '',
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (! photo) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            <span class="text-white font-exo uppercase tracking-wide">Thông tin người dùng</span>
        </template>

        <template #description>
            <span class="text-slate-400">Khu vực cập nhật avatar, bio, tên của người dùng </span>
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <input id="photo" ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview">
                <InputLabel for="photo" value="Avatar" />

                <div class="flex items-center gap-6 mt-2">
                    <div v-show="!photoPreview" class="relative group">
                        <img :src="user.profile_photo_url" :alt="user.name" class="rounded-xl h-20 w-20 object-cover border-2 border-slate-700 shadow-lg group-hover:border-cyan-500 transition-colors">
                        <div class="absolute inset-0 bg-cyan-500/10 rounded-xl opacity-0 group-hover:opacity-100 transition"></div>
                    </div>

                    <div v-show="photoPreview" class="relative">
                        <span class="block rounded-xl h-20 w-20 bg-cover bg-no-repeat bg-center border-2 border-cyan-500 shadow-[0_0_15px_cyan]"
                              :style="'background-image: url(\'' + photoPreview + '\');'" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <SecondaryButton class="!text-[10px] !py-2 !bg-slate-800 !border-slate-600 hover:!border-cyan-500" type="button" @click.prevent="selectNewPhoto">
                            Select New Image
                        </SecondaryButton>

                        <button v-if="user.profile_photo_path" type="button" class="text-[10px] font-bold text-rose-500 hover:text-rose-400 uppercase tracking-widest text-left" @click.prevent="deletePhoto">
                            Remove Avatar
                        </button>
                    </div>
                </div>
                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Tên người dùng" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full bg-slate-900 border-slate-700 focus:border-cyan-500 focus:ring-cyan-500/20 text-white font-exo" required autocomplete="name" />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email người dùng" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full bg-slate-900 border-slate-700 focus:border-cyan-500 focus:ring-cyan-500/20 text-white font-mono text-sm" required autocomplete="username" />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2 text-rose-400 font-mono">
                        >> EMAIL UNVERIFIED.
                        <Link :href="route('verification.send')" method="post" as="button" class="underline text-cyan-400 hover:text-cyan-300 ml-1">
                            Resend verification signal.
                        </Link>
                    </p>
                    <div v-show="verificationLinkSent" class="mt-2 font-bold text-xs text-emerald-400 font-mono">
                        >> NEW LINK DISPATCHED TO INBOX.
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="bio" value="Hồ sơ người dùng" />
                <textarea
                    id="bio"
                    v-model="form.bio"
                    class="mt-1 block w-full bg-slate-900 border-slate-700 text-slate-300 focus:border-cyan-500 focus:ring-cyan-500/20 rounded-xl shadow-sm text-sm font-sans p-3 min-h-[100px]"
                    placeholder="Describe your skillset..."
                ></textarea>
                <InputError :message="form.errors.bio" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3 text-emerald-400 font-mono text-xs uppercase tracking-widest">
                Update Complete
            </ActionMessage>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save Profile
            </PrimaryButton>
        </template>
    </FormSection>
</template>