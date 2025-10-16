import PageBreadcrumb from "../../admin-components/common/PageBreadCrumb";
import ResponsiveImage from "../../admin-components/ui/images/ResponsiveImage";
import TwoColumnImageGrid from "../../admin-components/ui/images/TwoColumnImageGrid";
import ThreeColumnImageGrid from "../../admin-components/ui/images/ThreeColumnImageGrid";
import ComponentCard from "../../admin-components/common/ComponentCard";
import PageMeta from "../../admin-components/common/PageMeta";

export default function Images() {
  return (
    <>
      <PageMeta
        title="React.js Images Dashboard | TailAdmin - React.js Admin Dashboard Template"
        description="This is React.js Images page for TailAdmin - React.js Tailwind CSS Admin Dashboard Template"
      />
      <PageBreadcrumb pageTitle="Images" />
      <div className="space-y-5 sm:space-y-6">
        <ComponentCard title="Responsive image">
          <ResponsiveImage />
        </ComponentCard>
        <ComponentCard title="Image in 2 Grid">
          <TwoColumnImageGrid />
        </ComponentCard>
        <ComponentCard title="Image in 3 Grid">
          <ThreeColumnImageGrid />
        </ComponentCard>
      </div>
    </>
  );
}
