<?
$strSQL = "SELECT cst.customerid, cstobj.name, dist.description, 

                                (CASE WHEN cnt.buildingnumber IS NULL THEN '' ELSE cnt.buildingnumber END) || (CASE WHEN cnt.streetname IS NULL THEN '' ELSE cnt.streetname END) || ', ' || (CASE WHEN dist.description IS NULL THEN '' ELSE dist.description END) || ', ' || (CASE WHEN city.description IS NULL THEN '' ELSE city.description END) || ', Flat : ' ||(CASE WHEN cnt.flatnumber IS NULL THEN '' ELSE cnt.flatnumber END)  AS address,

                                (CASE WHEN cnt.homephoneareacode IS NULL THEN '' ELSE cnt.homephoneareacode::text END) || '-' || (CASE WHEN cnt.homephonenumber IS NULL THEN '' ELSE cnt.homephonenumber::text END ) as homephone ,

                                (CASE WHEN cnt.workphoneareacode IS NULL THEN '' ELSE cnt.workphoneareacode::text END) || '-' || (CASE WHEN cnt.workphonenumber IS NULL THEN '' ELSE cnt.workphonenumber::text END ) as workphone, 

                                (CASE WHEN cnt.mobile1networkprefix IS NULL THEN '' ELSE cnt.mobile1networkprefix::text END) || '-' || (CASE WHEN cnt.mobile1number IS NULL THEN '' ELSE cnt.mobile1number::text END ) as mobile1, 

                                (CASE WHEN cnt.mobile2networkprefix IS NULL THEN '' ELSE cnt.mobile2networkprefix::text END) || '-' || (CASE WHEN cnt.mobile2number IS NULL THEN '' ELSE cnt.mobile2number::text END ) as mobile2,

                                (CASE WHEN cnt.faxareacode IS NULL THEN '' ELSE cnt.faxareacode::text END) || '-' || (CASE WHEN cnt.faxnumber IS NULL THEN '' ELSE cnt.faxnumber::text END ) as fax ,

                                ( CASE WHEN localloop THEN 'Local Loop' Else ( aap.areacode || '-' || aap.phonenumber ) end) as dslphone, 

                                aap.areacode,

                                (CASE WHEN localloop THEN 'Local Loop' Else aap.phonenumber::text END ) as phonenumber,

                                (lineownerfirstname || ' ' || (CASE WHEN lineownermiddlename IS NULL THEN '' ELSE lineownermiddlename END) || ' ' || (CASE WHEN lineownerlastname IS NULL THEN '' ELSE lineownerlastname END) ) as lineowner, 

                                aap.adslexchangeid, aap.emailusername, (CASE WHEN cst.isflex THEN 'FLEX' ELSE 'NORMAL' END ) as model,

                                (CASE WHEN (position('Basic' in p.name) <> 0) Then '1'

                                 WHEN (position('Bronze' in p.name) <> 0) Then '2'

                                 WHEN (position('Silver' in p.name) <> 0) Then '3'

                                 WHEN (position('Gold' in p.name) <> 0) Then '4'

                                 WHEN (position('Platinum' in p.name) <> 0) Then '5'

                                 WHEN (position('Business' in p.name) <> 0) Then '6'                       

         ELSE ''

                                 END) AS hostingpackage,

                                 (CASE WHEN p.name ilike '%ADSL%limit%' THEN '1' ELSE '0' END) as limited,

                                 (CASE WHEN p.name ILIKE '%khadamaty%' THEN '1' ELSE '0' END) as khadamaty,

                                 p.downloadspeed || '/' || p.uploadratio AS speedName,
                                 oc.objectcategoryid as segmentation_category
                                 

                                FROM smb_customers cst

                                INNER JOIN smb_accounts acc ON (cst.customerid = acc.customerid and acc.serviceid IN (69)) 

                                LEFT JOIN smb_adslaccountproperties aap ON (acc.accountid = aap.accountid)

                                INNER JOIN shr_objects cstobj ON (cst.objectid = cstobj.objectid)

                                INNER JOIN smb_contacts cnt ON (cst.contactid = cnt.contactid)

                                LEFT JOIN smb_vwpackages p ON (p.packageid = acc.packageid)  

                                LEFT JOIN (shr_districts dist INNER JOIN shr_cities city ON (dist.cityid = city.cityid) )  ON (cnt.districtid = dist.districtid)
                                LEFT JOIN shr_objectcategories oc on oc.objectid = cst.objectid and oc.objectcategoryid = 3202 ";
?>	