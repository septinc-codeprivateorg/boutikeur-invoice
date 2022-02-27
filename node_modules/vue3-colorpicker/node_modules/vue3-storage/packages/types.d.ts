export declare enum StorageType {
    Session = "session",
    Local = "local",
    WebSQL = "webSQL",
    IndexDB = "indexDB"
}
export interface StorageConfig {
    namespace?: string | false;
    storage?: StorageType;
}
export interface StorageData {
    value: any;
    expire: number | null;
}
export interface CallbackResult {
    errMsg: string;
}
export interface GetStorageOption<T> {
    key: string;
    complete?: (res: CallbackResult) => void;
    fail?: (res: CallbackResult) => void;
    success?: (result: GetStorageSuccessCallbackResult<T>) => void;
}
export interface GetStorageSuccessCallbackResult<T> extends CallbackResult {
    data: T;
    errMsg: string;
}
export interface SetStorageOption {
    data: any;
    key: string;
    expire?: number;
    complete?: (res: CallbackResult) => void;
    fail?: (res: CallbackResult) => void;
    success?: (res: CallbackResult) => void;
}
export interface RemoveStorageOption {
    namespace?: string;
    key: string;
    complete?: (res: CallbackResult) => void;
    fail?: (res: CallbackResult) => void;
    success?: (res: CallbackResult) => void;
}
export interface ClearStorageOption {
    namespace?: string;
    complete?: (res: CallbackResult) => void;
    fail?: (res: CallbackResult) => void;
    success?: (res: CallbackResult) => void;
}
export interface GetStorageInfoOption {
    currentSize: number;
    keys: string[];
    limitSize: number;
    keysLength: number;
}
export interface GetStorageInfoSuccessCallbackOption {
    complete?: (res: CallbackResult) => void;
    fail?: (res: CallbackResult) => void;
    success?: (option: GetStorageInfoOption) => void;
}
export interface StorageInterface {
    getStorage<T = any>(option: GetStorageOption<T>): Promise<GetStorageSuccessCallbackResult<T>>;
    getStorageSync<T = any>(key: string): T | undefined;
    setStorageSync(key: string, data: any, expire?: number): void;
    setStorage(option: SetStorageOption): Promise<CallbackResult>;
    isExpire(key: string): boolean;
    key(index: number): string | null;
    hasKey(key: string): boolean;
    removeStorage(option: RemoveStorageOption): Promise<CallbackResult>;
    removeStorageSync(name: string): void;
    getStorageInfo(option?: GetStorageInfoSuccessCallbackOption): Promise<CallbackResult>;
    getStorageInfoSync(): GetStorageInfoOption;
    clearStorage(option?: ClearStorageOption): Promise<CallbackResult>;
    clearStorageSync(): void;
    config(namespace?: string): void;
}
declare module "@vue/runtime-core" {
    interface ComponentCustomProperties {
        $storage: StorageInterface;
    }
}
