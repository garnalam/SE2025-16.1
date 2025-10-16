import PageBreadcrumb from "../../admin-components/common/PageBreadCrumb";
import DefaultInputs from "../../admin-components/form/form-elements/DefaultInputs";
import InputGroup from "../../admin-components/form/form-elements/InputGroup";
import DropzoneComponent from "../../admin-components/form/form-elements/DropZone";
import CheckboxComponents from "../../admin-components/form/form-elements/CheckboxComponents";
import RadioButtons from "../../admin-components/form/form-elements/RadioButtons";
import ToggleSwitch from "../../admin-components/form/form-elements/ToggleSwitch";
import FileInputExample from "../../admin-components/form/form-elements/FileInputExample";
import SelectInputs from "../../admin-components/form/form-elements/SelectInputs";
import TextAreaInput from "../../admin-components/form/form-elements/TextAreaInput";
import InputStates from "../../admin-components/form/form-elements/InputStates";
import PageMeta from "../../admin-components/common/PageMeta";

export default function FormElements() {
  return (
    <div>
      <PageMeta
        title="React.js Form Elements Dashboard | TailAdmin - React.js Admin Dashboard Template"
        description="This is React.js Form Elements  Dashboard page for TailAdmin - React.js Tailwind CSS Admin Dashboard Template"
      />
      <PageBreadcrumb pageTitle="From Elements" />
      <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div className="space-y-6">
          <DefaultInputs />
          <SelectInputs />
          <TextAreaInput />
          <InputStates />
        </div>
        <div className="space-y-6">
          <InputGroup />
          <FileInputExample />
          <CheckboxComponents />
          <RadioButtons />
          <ToggleSwitch />
          <DropzoneComponent />
        </div>
      </div>
    </div>
  );
}
