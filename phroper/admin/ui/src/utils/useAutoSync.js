import { useCallback, useEffect, useRef, useState } from "react";

/**
 * This hook is calls a given updater function periodically when the data is updated
 * @param {async function} updateFunction This function is called to save the data
 * @param {any} initialValue initial value of the stored data
 * @param {boolean} updateAlways disables dirty value check and runs update on every interval tick (default false)
 * @param {number} intervalTime delay between updates in  milliseconds (default 3000)
 */

export default function useAutoSync(
  updateFunction,
  initialValue = null,
  updateAlways = false,
  intervalTime = 3000
) {
  const syncDataRef = useRef({
    isSyncing: false,
    isDirty: false,
    updateAlways: updateAlways,
    value: initialValue,
  });
  syncDataRef.current.updateFunction = updateFunction;
  syncDataRef.current.updateAlways = updateAlways;
  const [isSyncing, setIsSyncing] = useState(false);

  // function that handles synchronization logic
  const intervalHandler = useCallback(async () => {
    // Exit if syncronization is not necessary
    if (!syncDataRef.current.isDirty && !syncDataRef.current.updateAlways)
      return;
    if (syncDataRef.current.isSyncing) return;

    // Update code field on server
    try {
      syncDataRef.current.isDirty = false;
      syncDataRef.current.isSyncing = true;
      setIsSyncing(true);
      await syncDataRef.current.updateFunction(syncDataRef.current.value);
    } catch {
      syncDataRef.current.isDirty = true;
    }
    syncDataRef.current.isSyncing = false;
    setIsSyncing(false);
  }, [syncDataRef, setIsSyncing]);

  // Register and release interval resource
  useEffect(() => {
    const interval = setInterval(intervalHandler, intervalTime);
    return () => clearInterval(interval);
  }, [intervalTime, intervalHandler]);

  // Function that updates internal data
  const handleChange = useCallback(
    (newData) => {
      const nd =
        typeof newData === "function"
          ? newData(syncDataRef.current.value)
          : newData;
      if (nd !== syncDataRef.current.value) {
        syncDataRef.current.value = nd;
        syncDataRef.current.isDirty = true;
      }
    },
    [syncDataRef]
  );

  return [handleChange, isSyncing];
}
