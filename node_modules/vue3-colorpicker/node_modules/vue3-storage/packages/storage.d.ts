import { CallbackResult, ClearStorageOption, GetStorageInfoOption, GetStorageInfoSuccessCallbackOption, GetStorageOption, GetStorageSuccessCallbackResult, RemoveStorageOption, SetStorageOption, StorageInterface } from "./types";
export declare class StorageClass implements StorageInterface {
    protected storage: Storage;
    protected namespace: string;
    constructor(storage?: Storage);
    config(namespace?: string | false): void;
    clearStorage(option?: ClearStorageOption): Promise<CallbackResult>;
    clearStorageSync(): void;
    getStorage<T = any>(option: GetStorageOption<T>): Promise<GetStorageSuccessCallbackResult<T>>;
    getStorageInfo(option?: GetStorageInfoSuccessCallbackOption): Promise<CallbackResult>;
    getStorageInfoSync(): GetStorageInfoOption;
    hasKey(key: string): boolean;
    isExpire(key: string): boolean;
    key(index: number): string | null;
    removeStorage(option: RemoveStorageOption): Promise<CallbackResult>;
    removeStorageSync(key: string): void;
    setStorage(option: SetStorageOption): Promise<CallbackResult>;
    setStorageSync(key: string, data: any, expire?: number): void;
    private getItemKey;
    private getItem;
    getStorageSync<T = any>(key: string): T | undefined;
}
