import AssetTypeProvider from "./Asset/AssetTypeContext";

/**
 * This is used to wrap all contexts into one component.
 * @param props Allows nested components
 * @returns <Context> </Context>
 */
export default function Contexts(props: any) {
  return (
    <>
      <AssetTypeProvider>{props.children}</AssetTypeProvider>
    </>
  );
}
