import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";

const STORAGE_KEY = "admin_profile_state";

export interface ProfileGeneral {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  bio: string;
  location: string;
}

export interface ProfileSocial {
  facebook: string;
  twitter: string;
  linkedin: string;
  instagram: string;
}

export interface ProfileAddress {
  country: string;
  cityState: string;
  postalCode: string;
  taxId: string;
}

export interface ProfileData {
  general: ProfileGeneral;
  social: ProfileSocial;
  address: ProfileAddress;
}

export interface ProfileContextValue {
  data: ProfileData;
  updateGeneral: (changes: Partial<ProfileGeneral>) => Promise<void>;
  updateSocial: (changes: Partial<ProfileSocial>) => Promise<void>;
  updateAddress: (changes: Partial<ProfileAddress>) => Promise<void>;
  updateAll: (changes: Partial<ProfileData>) => Promise<void>;
  isLoading: boolean;
  showToast: (message: string, type: 'success' | 'error') => void;
}

const defaultProfile: ProfileData = {
  general: {
    firstName: "Musharof",
    lastName: "Chowdhury",
    email: "randomuser@pimjo.com",
    phone: "+09 363 398 46",
    bio: "Team Manager",
    location: "Arizona, United States",
  },
  social: {
    facebook: "https://www.facebook.com/PimjoHQ",
    twitter: "https://x.com/PimjoHQ",
    linkedin: "https://www.linkedin.com/company/pimjo",
    instagram: "https://instagram.com/PimjoHQ",
  },
  address: {
    country: "United States",
    cityState: "Phoenix, Arizona, United States",
    postalCode: "ERT 2489",
    taxId: "AS4568384",
  },
};

const ProfileContext = createContext<ProfileContextValue | undefined>(undefined);

const readFromStorage = (): ProfileData => {
  if (typeof window === "undefined") {
    return defaultProfile;
  }

  try {
    const stored = window.localStorage.getItem(STORAGE_KEY);
    if (!stored) {
      return defaultProfile;
    }

    const parsed = JSON.parse(stored) as Partial<ProfileData>;

    return {
      general: {
        ...defaultProfile.general,
        ...(parsed.general ?? {}),
      },
      social: {
        ...defaultProfile.social,
        ...(parsed.social ?? {}),
      },
      address: {
        ...defaultProfile.address,
        ...(parsed.address ?? {}),
      },
    };
  } catch (error) {
    console.error("Failed to parse profile state", error);
    return defaultProfile;
  }
};

// Toast notification system
interface Toast {
  id: string;
  message: string;
  type: 'success' | 'error';
}

let toastContainer: HTMLDivElement | null = null;

const createToastContainer = () => {
  if (toastContainer) return toastContainer;
  
  toastContainer = document.createElement('div');
  toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
  document.body.appendChild(toastContainer);
  return toastContainer;
};

const showToastNotification = (message: string, type: 'success' | 'error') => {
  const container = createToastContainer();
  const toastId = `toast-${Date.now()}`;
  
  const toast = document.createElement('div');
  toast.id = toastId;
  toast.className = `
    px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out
    ${type === 'success' 
      ? 'bg-green-500 text-white border-l-4 border-green-600' 
      : 'bg-red-500 text-white border-l-4 border-red-600'
    }
    animate-slide-in-right max-w-sm
  `;
  
  toast.innerHTML = `
    <div class="flex items-center">
      <div class="flex-shrink-0">
        ${type === 'success' 
          ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
          : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
        }
      </div>
      <div class="ml-3">
        <p class="text-sm font-medium">${message}</p>
      </div>
    </div>
  `;
  
  container.appendChild(toast);
  
  // Auto remove after 4 seconds
  setTimeout(() => {
    toast.style.transform = 'translateX(100%)';
    toast.style.opacity = '0';
    setTimeout(() => {
      if (container.contains(toast)) {
        container.removeChild(toast);
      }
    }, 300);
  }, 4000);
};

// Validation functions
const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

const validatePhone = (phone: string): boolean => {
  const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
  return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
};

const validateUrl = (url: string): boolean => {
  if (!url) return true; // Optional field
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
};

export const ProfileProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [data, setData] = useState<ProfileData>(() => readFromStorage());
  const [isHydrated, setIsHydrated] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (!isHydrated && typeof window !== "undefined") {
      setData(readFromStorage());
      setIsHydrated(true);
    }
  }, [isHydrated]);

  useEffect(() => {
    if (typeof window !== "undefined") {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }
  }, [data]);

  const showToast = useCallback((message: string, type: 'success' | 'error') => {
    showToastNotification(message, type);
  }, []);

  const updateGeneral = useCallback(async (changes: Partial<ProfileGeneral>) => {
    // Validation
    if (changes.email && !validateEmail(changes.email)) {
      showToast("Email không hợp lệ!", 'error');
      throw new Error("Invalid email format");
    }
    
    if (changes.phone && !validatePhone(changes.phone)) {
      showToast("Số điện thoại không hợp lệ!", 'error');
      throw new Error("Invalid phone format");
    }

    setIsLoading(true);
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 800));
      
      setData((previous) => ({
        ...previous,
        general: {
          ...previous.general,
          ...changes,
        },
      }));
      
      showToast("Thông tin cá nhân đã được cập nhật thành công!", 'success');
    } catch (error) {
      showToast("Có lỗi xảy ra khi cập nhật thông tin!", 'error');
      throw error;
    } finally {
      setIsLoading(false);
    }
  }, [showToast]);

  const updateSocial = useCallback(async (changes: Partial<ProfileSocial>) => {
    // Validation for social URLs
    const socialFields = ['facebook', 'twitter', 'linkedin', 'instagram'] as const;
    for (const field of socialFields) {
      if (changes[field] && !validateUrl(changes[field]!)) {
        showToast(`URL ${field} không hợp lệ!`, 'error');
        throw new Error(`Invalid ${field} URL`);
      }
    }

    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 800));
      
      setData((previous) => ({
        ...previous,
        social: {
          ...previous.social,
          ...changes,
        },
      }));
      
      showToast("Liên kết mạng xã hội đã được cập nhật thành công!", 'success');
    } catch (error) {
      showToast("Có lỗi xảy ra khi cập nhật liên kết mạng xã hội!", 'error');
      throw error;
    } finally {
      setIsLoading(false);
    }
  }, [showToast]);

  const updateAddress = useCallback(async (changes: Partial<ProfileAddress>) => {
    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 800));
      
      setData((previous) => ({
        ...previous,
        address: {
          ...previous.address,
          ...changes,
        },
      }));
      
      showToast("Địa chỉ đã được cập nhật thành công!", 'success');
    } catch (error) {
      showToast("Có lỗi xảy ra khi cập nhật địa chỉ!", 'error');
      throw error;
    } finally {
      setIsLoading(false);
    }
  }, [showToast]);

  const updateAll = useCallback(async (changes: Partial<ProfileData>) => {
    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      setData((previous) => ({
        general: {
          ...previous.general,
          ...(changes.general ?? {}),
        },
        social: {
          ...previous.social,
          ...(changes.social ?? {}),
        },
        address: {
          ...previous.address,
          ...(changes.address ?? {}),
        },
      }));
      
      showToast("Tất cả thông tin đã được cập nhật thành công!", 'success');
    } catch (error) {
      showToast("Có lỗi xảy ra khi cập nhật thông tin!", 'error');
      throw error;
    } finally {
      setIsLoading(false);
    }
  }, [showToast]);

  const value = useMemo<ProfileContextValue>(
    () => ({
      data,
      updateGeneral,
      updateSocial,
      updateAddress,
      updateAll,
      isLoading,
      showToast,
    }),
    [data, updateGeneral, updateSocial, updateAddress, updateAll, isLoading, showToast],
  );

  return <ProfileContext.Provider value={value}>{children}</ProfileContext.Provider>;
};

export const useProfile = (): ProfileContextValue => {
  const context = useContext(ProfileContext);

  if (!context) {
    throw new Error("useProfile must be used within a ProfileProvider");
  }

  return context;
};